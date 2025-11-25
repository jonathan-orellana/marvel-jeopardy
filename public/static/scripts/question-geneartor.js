import md5 from "blueimp-md5";

const MARVEL_PUBLIC_KEY = "YOUR_PUBLIC_KEY";
const MARVEL_PRIVATE_KEY = "YOUR_PRIVATE_KEY";
const MARVEL_API_BASE_URL = "https://gateway.marvel.com/v1/public";


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

function fetchMarvelItems(endpointPath, queryParams) {
  const url = buildMarvelApiUrl(endpointPath, queryParams);

  return $.ajax({
    url,
    method: "GET",
    dataType: "json",
  }).then((json) => {
    return json.data.results;
  }).catch((xhr, status, error) => {
    throw new Error(`Marvel API request failed: ${xhr.status} ${error}`);
  });
}

async function fetchItemsByCategory() {
  const [characters, events, creators, comics] = await Promise.all([
    fetchMarvelItems("/characters", { limit: 60, orderBy: "-modified" }),
    fetchMarvelItems("/events", { limit: 60, orderBy: "-modified" }),
    fetchMarvelItems("/creators", { limit: 60, orderBy: "-modified" }),
    fetchMarvelItems("/comics", { limit: 80, orderBy: "-onsaleDate" }),
  ]);

  return {
    character: characters,
    event: events,
    author: creators,
    movies: comics,
    quotes: comics,
  };
}

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

export async function generateJeopardyQuestions() {
  const itemsByCategory = await fetchItemsByCategory();
  const categoryOrder = ["author", "event", "character", "movies", "quotes"];

  let allQuestions = [];

  for (const category of categoryOrder) {
    const categoryQuestions = buildQuestionsForCategory(
      category,
      itemsByCategory[category],
      5
    );
    allQuestions.push(...categoryQuestions);
  }

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

  allQuestions = allQuestions.slice(0, 25);

  for (let i = allQuestions.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [allQuestions[i], allQuestions[j]] = [allQuestions[j], allQuestions[i]];
  }

  return allQuestions;
}
