/*
What this code does:

Calls the Marvel API to get fresh lists of characters, events, creators, and comics.

Treats those as 5 categories: author, event, character, movies, quotes.

Filters out weak items (missing name/title, bad thumbnail, no description where needed).

Picks valid items until it gets 5 per category (25 total). If it comes up short, it refetches and tops up.

For each chosen item, it:

builds a trivia prompt,

computes a difficulty score using popularity, age, specificity, and name complexity,

maps that score to 100–500,

randomly assigns a question type (true/false, multiple choice, or response),

adds choices for multiple choice and true/false.

Returns a shuffled array of 25 ready-to-play Jeopardy questions without saving anything to your database
*/

/* 
  Marvel Jeopardy Question Generator (No Database)

  What you need:
  1) Install an md5 library (example):
     npm install blueimp-md5
  2) Put your Marvel keys below.
*/

import md5 from "blueimp-md5";

const MARVEL_PUBLIC_KEY = "YOUR_PUBLIC_KEY";
const MARVEL_PRIVATE_KEY = "YOUR_PRIVATE_KEY";
const MARVEL_API_BASE_URL = "https://gateway.marvel.com/v1/public";

/* ----------------------------- API HELPERS ----------------------------- */

function buildMarvelApiUrl(endpointPath, queryParams = {}) {
  const timestamp = Date.now().toString();
  const hash = md5(timestamp + MARVEL_PRIVATE_KEY + MARVEL_PUBLIC_KEY);

  const searchParams = new URLSearchParams({
    ...queryParams,
    ts: timestamp,
    apikey: MARVEL_PUBLIC_KEY,
    hash: hash,
  });

  return `${MARVEL_API_BASE_URL}${endpointPath}?${searchParams.toString()}`;
}

async function fetchMarvelItems(endpointPath, queryParams) {
  const url = buildMarvelApiUrl(endpointPath, queryParams);
  const response = await fetch(url);

  if (!response.ok) {
    throw new Error(`Marvel API request failed: ${response.status}`);
  }

  const json = await response.json();
  return json.data.results;
}

/* ----------------------- GET RAW ITEMS PER CATEGORY ----------------------- */

async function fetchItemsByCategory() {
  const [characters, events, creators, comics] = await Promise.all([
    fetchMarvelItems("/characters", { limit: 60, orderBy: "-modified" }),
    fetchMarvelItems("/events", { limit: 60, orderBy: "-modified" }),
    fetchMarvelItems("/creators", { limit: 60, orderBy: "-modified" }),
    fetchMarvelItems("/comics", { limit: 80, orderBy: "-onsaleDate" }),
  ]);

  // "movies" and "quotes" are seeded from comics (Marvel API doesn’t provide direct movie/quote endpoints)
  return {
    character: characters,
    event: events,
    author: creators,
    movies: comics,
    quotes: comics,
  };
}

/* -------------------------- VALIDATION HELPERS -------------------------- */

function itemHasUsableThumbnail(item) {
  return (
    item.thumbnail &&
    item.thumbnail.path &&
    !item.thumbnail.path.includes("image_not_available")
  );
}

function itemHasNonEmptyText(text, minLength = 5) {
  return typeof text === "string" && text.trim().length >= minLength;
}

function isMarvelItemValidForCategory(category, item) {
  const displayName = item.name || item.title || item.fullName;
  if (!itemHasNonEmptyText(displayName, 1)) return false;

  if (category === "character" || category === "event") {
    return (
      itemHasUsableThumbnail(item) &&
      itemHasNonEmptyText(item.description, 10)
    );
  }

  if (category === "author") {
    return (item.comics?.available || 0) > 0;
  }

  if (category === "movies" || category === "quotes") {
    return itemHasUsableThumbnail(item) && itemHasNonEmptyText(item.title, 3);
  }

  return true;
}

function filterValidItems(category, items) {
  return items.filter((item) => isMarvelItemValidForCategory(category, item));
}

/* ------------------------------ RANDOM HELPERS ------------------------------ */

function chooseRandomElement(array) {
  return array[Math.floor(Math.random() * array.length)];
}

function chooseUniqueRandomElements(array, count) {
  const copy = [...array];
  const result = [];

  while (result.length < count && copy.length > 0) {
    const index = Math.floor(Math.random() * copy.length);
    result.push(copy.splice(index, 1)[0]);
  }

  return result;
}

function chooseValidUniqueItems(category, items, neededCount, maxAttempts = 500) {
  const validPool = filterValidItems(category, items);
  const chosenItems = [];
  const usedIds = new Set();

  let attempts = 0;
  while (chosenItems.length < neededCount && attempts < maxAttempts) {
    attempts++;
    const candidate = chooseRandomElement(validPool);
    if (!candidate) break;
    if (usedIds.has(candidate.id)) continue;

    usedIds.add(candidate.id);
    chosenItems.push(candidate);
  }

  return chosenItems;
}

/* -------------------------- DIFFICULTY SCORING -------------------------- */

function clampBetweenZeroAndOne(value) {
  return Math.max(0, Math.min(1, value));
}

function normalizeToZeroOne(value, min, max) {
  if (max === min) return 0.5;
  return clampBetweenZeroAndOne((value - min) / (max - min));
}

function computeItemStats(items) {
  const currentYear = new Date().getFullYear();

  const appearanceCounts = [];
  const nameLengths = [];
  const ages = [];

  for (const item of items) {
    const appearances =
      (item.comics?.available || 0) +
      (item.series?.available || 0) +
      (item.stories?.available || 0) +
      (item.events?.available || 0);

    appearanceCounts.push(appearances);

    const nameText = item.name || item.title || item.fullName || "";
    nameLengths.push(nameText.length);

    let year = item.startYear || currentYear;
    if (item.dates?.length) {
      const onSaleDate = item.dates.find((d) => d.type === "onsaleDate")?.date;
      if (onSaleDate) year = new Date(onSaleDate).getFullYear();
    }
    ages.push(currentYear - year);
  }

  return {
    currentYear,
    minAppearances: Math.min(...appearanceCounts),
    maxAppearances: Math.max(...appearanceCounts),
    minNameLength: Math.min(...nameLengths),
    maxNameLength: Math.max(...nameLengths),
    minAge: Math.min(...ages),
    maxAge: Math.max(...ages),
  };
}

function computeDifficultyScore(item, stats) {
  // Popularity (many appearances = easier)
  const appearanceCount =
    (item.comics?.available || 0) +
    (item.series?.available || 0) +
    (item.stories?.available || 0) +
    (item.events?.available || 0);

  const popularityScore = normalizeToZeroOne(
    appearanceCount,
    stats.minAppearances,
    stats.maxAppearances
  );
  const lessPopularMeansHarder = 1 - popularityScore;

  // Age (older = harder)
  let year = item.startYear || stats.currentYear;
  if (item.dates?.length) {
    const onSaleDate = item.dates.find((d) => d.type === "onsaleDate")?.date;
    if (onSaleDate) year = new Date(onSaleDate).getFullYear();
  }

  const itemAge = stats.currentYear - year;
  const ageScore = normalizeToZeroOne(itemAge, stats.minAge, stats.maxAge);

  // Specificity (fewer sources = harder)
  const sourcesCount = Math.max(
    1,
    item.comics?.available || item.events?.available || 1
  );
  const specificityScore = clampBetweenZeroAndOne(
    1 / Math.log2(sourcesCount + 1)
  );

  // Name complexity (longer = harder)
  const nameText = item.name || item.title || item.fullName || "";
  const nameScore = normalizeToZeroOne(
    nameText.length,
    stats.minNameLength,
    stats.maxNameLength
  );

  // Weighted mix
  let difficulty =
    0.50 * lessPopularMeansHarder +
    0.20 * ageScore +
    0.20 * specificityScore +
    0.10 * nameScore;

  // Small randomness so it doesn’t feel repetitive
  difficulty += (Math.random() - 0.5) * 0.08;

  return clampBetweenZeroAndOne(difficulty);
}

function mapDifficultyToJeopardyValue(difficultyScore) {
  if (difficultyScore <= 0.20) return 100;
  if (difficultyScore <= 0.40) return 200;
  if (difficultyScore <= 0.60) return 300;
  if (difficultyScore <= 0.80) return 400;
  return 500;
}

/* ---------------------------- QUESTION TYPES ---------------------------- */

function pickRandomQuestionType() {
  const roll = Math.random();
  if (roll < 0.33) return "tf";
  if (roll < 0.66) return "mcq";
  return "response";
}

function buildMultipleChoiceOptions(correctItem, poolItems, getDisplayName, optionCount = 4) {
  const correctAnswer = getDisplayName(correctItem);

  const wrongOptions = chooseUniqueRandomElements(
    poolItems.filter((item) => getDisplayName(item) !== correctAnswer),
    optionCount - 1
  ).map(getDisplayName);

  const options = [...wrongOptions, correctAnswer];

  // Shuffle options
  for (let i = options.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [options[i], options[j]] = [options[j], options[i]];
  }

  return options;
}

/* ------------------------- QUESTION GENERATORS ------------------------- */

function createCharacterQuestion(characterItem, allCharacterItems) {
  const characterName = characterItem.name;

  const templates = [
    () => ({
      prompt: `Which character matches this description: "${characterItem.description.slice(0, 90)}..."?`,
      answer: characterName,
    }),
    () => ({
      prompt: `Who is this Marvel character: ${characterName}?`,
      answer: characterName,
    }),
  ];

  return chooseRandomElement(templates)();
}

function createEventQuestion(eventItem) {
  const eventTitle = eventItem.title;

  const templates = [
    () => ({
      prompt: `What Marvel event is called "${eventTitle}"?`,
      answer: eventTitle,
    }),
    () => ({
      prompt: `True or False: "${eventTitle}" involved many characters.`,
      answer: (eventItem.characters?.available || 0) > 5 ? "True" : "False",
      forcedType: "tf",
    }),
  ];

  return chooseRandomElement(templates)();
}

function createAuthorQuestion(creatorItem) {
  const creatorName =
    creatorItem.fullName ||
    `${creatorItem.firstName || ""} ${creatorItem.lastName || ""}`.trim();

  const templates = [
    () => ({
      prompt: `Which Marvel creator is named "${creatorName}"?`,
      answer: creatorName,
    }),
    () => ({
      prompt: `True or False: ${creatorName} has worked on more than 10 Marvel comics.`,
      answer: (creatorItem.comics?.available || 0) > 10 ? "True" : "False",
      forcedType: "tf",
    }),
  ];

  return chooseRandomElement(templates)();
}

function createMovieStyleQuestion(comicItem) {
  const comicTitle = comicItem.title;
  const onSaleDate = comicItem.dates?.find((d) => d.type === "onsaleDate")?.date;
  const year = onSaleDate ? new Date(onSaleDate).getFullYear() : null;

  const templates = [
    () => ({
      prompt: `Which comic title sounds like a movie storyline? "${comicTitle}"`,
      answer: comicTitle,
    }),
    () => ({
      prompt: `True or False: "${comicTitle}" was released after 2010.`,
      answer: year && year > 2010 ? "True" : "False",
      forcedType: "tf",
    }),
  ];

  return chooseRandomElement(templates)();
}

function createQuoteStyleQuestion(comicItem) {
  const comicTitle = comicItem.title;

  return {
    prompt: `This quote-style clue refers to which comic title: "${comicTitle}"?`,
    answer: comicTitle,
  };
}

const questionBuilders = {
  character: createCharacterQuestion,
  event: createEventQuestion,
  author: createAuthorQuestion,
  movies: createMovieStyleQuestion,
  quotes: createQuoteStyleQuestion,
};

/* ------------------- BUILD QUESTIONS FOR ONE CATEGORY ------------------- */

function buildQuestionsForCategory(category, categoryItems, desiredCount) {
  const stats = computeItemStats(categoryItems);
  const chosenItems = chooseValidUniqueItems(category, categoryItems, desiredCount);

  const getDisplayName = (item) => item.name || item.title || item.fullName;

  return chosenItems.map((item) => {
    const baseQuestion = questionBuilders[category](item, categoryItems);

    const difficultyScore = computeDifficultyScore(item, stats);
    const jeopardyValue = mapDifficultyToJeopardyValue(difficultyScore);

    const finalType = baseQuestion.forcedType || pickRandomQuestionType();

    const questionObject = {
      id: `${category}-${item.id}`,
      category,
      value: jeopardyValue,
      type: finalType,
      question: baseQuestion.prompt,
      answer: baseQuestion.answer,
      difficultyScore,
      source: {
        name: getDisplayName(item),
        marvelId: item.id,
      },
    };

    if (finalType === "mcq") {
      questionObject.choices = buildMultipleChoiceOptions(
        item,
        categoryItems,
        getDisplayName,
        4
      );
    }

    if (finalType === "tf") {
      questionObject.answer =
        questionObject.answer === "True" ? "True" : "False";
      questionObject.choices = ["True", "False"];
    }

    return questionObject;
  });
}

/* ------------------------- MAIN PUBLIC FUNCTION ------------------------- */

export async function generateJeopardyQuestions() {
  const itemsByCategory = await fetchItemsByCategory();
  const categoryOrder = ["author", "event", "character", "movies", "quotes"];

  let allQuestions = [];

  // First pass: try to get 5 per category
  for (const category of categoryOrder) {
    const categoryQuestions = buildQuestionsForCategory(
      category,
      itemsByCategory[category],
      5
    );
    allQuestions.push(...categoryQuestions);
  }

  // Top-up pass: if anything was short, refetch and fill missing slots
  let refillAttempts = 0;
  while (allQuestions.length < 25 && refillAttempts < 5) {
    refillAttempts++;

    const freshItems = await fetchItemsByCategory();

    for (const category of categoryOrder) {
      const alreadyHave = allQuestions.filter((q) => q.category === category).length;
      const stillNeed = 5 - alreadyHave;

      if (stillNeed > 0) {
        const extraQuestions = buildQuestionsForCategory(
          category,
          freshItems[category],
          stillNeed
        );
        allQuestions.push(...extraQuestions);
      }
    }
  }

  // Final trim + shuffle
  allQuestions = allQuestions.slice(0, 25);

  for (let i = allQuestions.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [allQuestions[i], allQuestions[j]] = [allQuestions[j], allQuestions[i]];
  }

  return allQuestions;
}
