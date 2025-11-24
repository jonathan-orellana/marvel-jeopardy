const categories = ["author", "character", "movies", "quotes", "event"];
const pointsOptions = [100, 200, 300, 400, 500];

const categoryQuestionMap = {};

let remainingCategories = [...categories];
let currentCategory = null;
let currentCategoryQuestions = [];

const WIZARD_STORAGE_KEY = "marvelJeopardyWizardState_v1";

function saveWizardState() {
  if (currentCategory) {
    syncDomToQuestions();
  }

  const state = {
    setTitle: document.getElementById("set-title")?.value.trim() ?? "",
    remainingCategories,
    currentCategory,
    currentCategoryQuestions,
    categoryQuestionMap,
    currentStepId: getCurrentStepId()
  };

  localStorage.setItem(WIZARD_STORAGE_KEY, JSON.stringify(state));
}

function loadWizardState() {
  const raw = localStorage.getItem(WIZARD_STORAGE_KEY);
  if (!raw) return false;

  try {
    const state = JSON.parse(raw);

    remainingCategories = state.remainingCategories ?? [...categories];
    currentCategory = state.currentCategory ?? null;
    currentCategoryQuestions = state.currentCategoryQuestions ?? [];
    Object.assign(categoryQuestionMap, state.categoryQuestionMap ?? {});

    const titleInput = document.getElementById("set-title");
    if (titleInput) {
      titleInput.value = state.setTitle ?? "";
    }

    restoreStepsUI(state.currentStepId);

    renderCategoryButtons();

    if (currentCategory) {
      document.querySelector("#category-title").textContent =
        `Category: ${capitalize(currentCategory)} (5 questions)`;

      renderQuestionsList();
      disableUsedPoints();
    }

    clearError();
    return true;
  } catch (e) {
    console.error("Failed to load wizard state:", e);
    return false;
  }
}

function clearWizardState() {
  localStorage.removeItem(WIZARD_STORAGE_KEY);
}

function getCurrentStepId() {
  const questionsStep = document.getElementById("questions-step");
  const categoryStep = document.getElementById("category-step");

  if (questionsStep?.classList.contains("current")) return "questions-step";
  if (categoryStep?.classList.contains("current")) return "category-step";
  return "set-title-step";
}

function restoreStepsUI(stepId) {
  const setTitleStep = document.getElementById("set-title-step");
  const categoryStep = document.getElementById("category-step");
  const questionsStep = document.getElementById("questions-step");

  [setTitleStep, categoryStep, questionsStep].forEach(step => {
    step.classList.remove("current");
    step.style.display = "none";
  });

  const stepToShow = document.getElementById(stepId || "set-title-step");
  stepToShow.style.display = "grid";
  stepToShow.classList.add("current");
}

document.addEventListener("input", (event) => {
  if (event.target.matches(
    "#set-title, .question-text, .answer-input, input[type='text']"
  )) {
    saveWizardState();
  }
});

document.addEventListener("change", (event) => {
  if (event.target.matches(
    ".question-type, .question-points, input[type='radio']"
  )) {
    saveWizardState();
  }
});

function MultipleChoiceQuestion(category) {
  return {
    id: Date.now() + Math.random(),
    category,
    type: "multipleChoice",
    points: null,
    text: "",
    options: ["", "", "", ""],
    correct: null,
  };
}

function TrueFalseQuestion(category) {
  return {
    id: Date.now() + Math.random(),
    category,
    type: "trueFalse",
    points: null,
    text: "",
    correct: null,
  };
}

function ResponseQuestion(category) {
  return {
    id: Date.now() + Math.random(),
    category,
    type: "response",
    points: null,
    text: "",
    correct: "",
  };
}

function PointsDropdownHTML(question, questionIndex) {
  const optionsHTML = pointsOptions.map(p => {
    const selected = question.points === p ? "selected" : "";
    return `<option value="${p}" ${selected}>${p}</option>`;
  }).join("");

  return `
    <label>Points:
      <select class="question-points" data-question-index="${questionIndex}">
        <option value="" disabled ${question.points == null ? "selected" : ""}>Select points</option>
        ${optionsHTML}
      </select>
    </label>
  `;
}

function MultipleChoiceQuestionHTML(question, questionIndex) {
  const checkedRadio = (i) => question.correct === i ? "checked" : "";
  const optionValue = (i) => question.options[i] ?? "";

  return `
    <div class="question-box" data-type="multipleChoice" data-question-index="${questionIndex}">
      <h2>Question ${questionIndex + 1}</h2>

      ${PointsDropdownHTML(question, questionIndex)}

      <label>Type:
        <select class="question-type" data-question-index="${questionIndex}">
          <option value="multipleChoice" selected>Multiple Choice</option>
          <option value="trueFalse">True or False</option>
          <option value="response">Response</option>
        </select>
      </label>

      <textarea class="question-text" placeholder="Enter question...">${question.text ?? ""}</textarea>

      <div>
        <input type="radio" name="correct-${questionIndex}" value="0" ${checkedRadio(0)}>
        <input type="text" data-option-index="${questionIndex}-0" placeholder="Option 1" value="${optionValue(0)}">
      </div>
      <div>
        <input type="radio" name="correct-${questionIndex}" value="1" ${checkedRadio(1)}>
        <input type="text" data-option-index="${questionIndex}-1" placeholder="Option 2" value="${optionValue(1)}">
      </div>
      <div>
        <input type="radio" name="correct-${questionIndex}" value="2" ${checkedRadio(2)}>
        <input type="text" data-option-index="${questionIndex}-2" placeholder="Option 3" value="${optionValue(2)}">
      </div>
      <div>
        <input type="radio" name="correct-${questionIndex}" value="3" ${checkedRadio(3)}>
        <input type="text" data-option-index="${questionIndex}-3" placeholder="Option 4" value="${optionValue(3)}">
      </div>
    </div>
  `;
}

function TrueFalseQuestionHTML(question, questionIndex) {
  const isTrue = question.correct === true ? "checked" : "";
  const isFalse = question.correct === false ? "checked" : "";

  return `
    <div class="question-box" data-type="trueFalse" data-question-index="${questionIndex}">
      <h2>Question ${questionIndex + 1}</h2>

      ${PointsDropdownHTML(question, questionIndex)}

      <label>Type:
        <select class="question-type" data-question-index="${questionIndex}">
          <option value="multipleChoice">Multiple Choice</option>
          <option value="trueFalse" selected>True or False</option>
          <option value="response">Response</option>
        </select>
      </label>

      <textarea class="question-text" placeholder="Enter question...">${question.text ?? ""}</textarea>

      <div>
        <label><input type="radio" name="tf-${questionIndex}" value="true" ${isTrue}> True</label>
        <label><input type="radio" name="tf-${questionIndex}" value="false" ${isFalse}> False</label>
      </div>
    </div>
  `;
}

function ResponseQuestionHTML(question, questionIndex) {
  return `
    <div class="question-box" data-type="response" data-question-index="${questionIndex}">
      <h2>Question ${questionIndex + 1}</h2>

      ${PointsDropdownHTML(question, questionIndex)}

      <label>Type:
        <select class="question-type" data-question-index="${questionIndex}">
          <option value="multipleChoice">Multiple Choice</option>
          <option value="trueFalse">True or False</option>
          <option value="response" selected>Response</option>
        </select>
      </label>

      <textarea class="question-text" placeholder="Enter question...">${question.text ?? ""}</textarea>
      <input class="answer-input" type="text" placeholder="Correct response" value="${question.correct ?? ""}">
    </div>
  `;
}

function renderCategoryButtons() {
  const container = document.querySelector("#category-buttons");
  container.innerHTML = "";

  remainingCategories.forEach((category) => {
    const button = document.createElement("button");
    button.className = "button";
    button.type = "button";
    button.textContent = capitalize(category);
    button.dataset.category = category;

    button.addEventListener("click", () => {
      startCategory(category);
    });

    container.appendChild(button);
  });

  if (remainingCategories.length === 0) {
    document.querySelector("#category-step").style.display = "none";
  }
}

function startCategory(category) {
  clearError();
  currentCategory = category;

  remainingCategories = remainingCategories.filter(c => c !== category);

  currentCategoryQuestions = [];
  for (let i = 0; i < 5; i++) {
    currentCategoryQuestions.push(MultipleChoiceQuestion(category));
  }

  const categoryStep = document.getElementById("category-step");
  const questionsStep = document.getElementById("questions-step");

  categoryStep.classList.remove("current");
  categoryStep.style.display = "none";

  questionsStep.style.display = "grid";
  questionsStep.classList.add("current");

  document.querySelector("#category-title").textContent =
    `Category: ${capitalize(category)} (5 questions)`;

  renderQuestionsList();
  renderCategoryButtons();
  saveWizardState();
}

function renderQuestionsList() {
  let questionListHTML = "";

  currentCategoryQuestions.forEach((question, questionIndex) => {
    let questionHTML = "";

    if (question.type === "multipleChoice") {
      questionHTML = MultipleChoiceQuestionHTML(question, questionIndex);
    }
    if (question.type === "trueFalse") {
      questionHTML = TrueFalseQuestionHTML(question, questionIndex);
    }
    if (question.type === "response") {
      questionHTML = ResponseQuestionHTML(question, questionIndex);
    }

    questionListHTML += questionHTML;
  });

  const container = document.querySelector("#questions");
  container.innerHTML = questionListHTML;

  bindQuestionTypeEvents();
  bindPointsEvents();
}

function syncDomToQuestions() {
  const questionBoxes = document.querySelectorAll(".question-box");

  questionBoxes.forEach((box, questionIndex) => {
    const question = currentCategoryQuestions[questionIndex];
    if (!question) return;

    const type = box.dataset.type;
    const textArea = box.querySelector(".question-text");
    const pointsSelect = box.querySelector(".question-points");

    if (textArea) question.text = textArea.value.trim();
    if (pointsSelect && pointsSelect.value !== "") {
      question.points = Number(pointsSelect.value);
    }

    if (type === "multipleChoice") {
      const optionInputs = box.querySelectorAll("input[type='text']");
      question.options = Array.from(optionInputs).map(i => i.value.trim());

      const selectedRadio = box.querySelector("input[type='radio']:checked");
      question.correct = selectedRadio ? Number(selectedRadio.value) : null;
    }

    if (type === "trueFalse") {
      const selectedRadio = box.querySelector("input[type='radio']:checked");
      question.correct =
        selectedRadio ? selectedRadio.value === "true" : null;
    }

    if (type === "response") {
      const answerInput = box.querySelector(".answer-input");
      question.correct = answerInput ? answerInput.value.trim() : "";
    }
  });
}

function bindQuestionTypeEvents() {
  const dropdowns = document.querySelectorAll(".question-type");

  dropdowns.forEach((dropdown) => {
    dropdown.addEventListener("change", (event) => {
      syncDomToQuestions();

      const selectedType = event.target.value;
      const questionIndex = Number(event.target.dataset.questionIndex);

      const oldQuestion = currentCategoryQuestions[questionIndex];
      let newQuestion;

      if (selectedType === "multipleChoice") newQuestion = MultipleChoiceQuestion(currentCategory);
      if (selectedType === "trueFalse") newQuestion = TrueFalseQuestion(currentCategory);
      if (selectedType === "response") newQuestion = ResponseQuestion(currentCategory);

      newQuestion.points = oldQuestion.points;
      newQuestion.text = oldQuestion.text;

      currentCategoryQuestions.splice(questionIndex, 1, newQuestion);

      renderQuestionsList();
      saveWizardState();
    });
  });
}

function bindPointsEvents() {
  const pointsDropdowns = document.querySelectorAll(".question-points");

  pointsDropdowns.forEach((dropdown) => {
    dropdown.addEventListener("change", (event) => {
      syncDomToQuestions();

      const questionIndex = Number(event.target.dataset.questionIndex);
      const pickedPoints = Number(event.target.value);

      currentCategoryQuestions[questionIndex].points = pickedPoints;
      disableUsedPoints();
      saveWizardState();
    });
  });

  disableUsedPoints();
}

function disableUsedPoints() {
  const used = currentCategoryQuestions
    .map(q => q.points)
    .filter(p => p !== null);

  const pointsDropdowns = document.querySelectorAll(".question-points");
  pointsDropdowns.forEach((dropdown, index) => {
    const currentValue = currentCategoryQuestions[index].points;

    Array.from(dropdown.options).forEach((opt) => {
      if (opt.value === "") return;

      const val = Number(opt.value);
      opt.disabled = used.includes(val) && val !== currentValue;
    });
  });
}

document.getElementById("go-to-category").addEventListener("click", () => {
  const title = document.getElementById("set-title").value.trim();

  if (!title) {
    showError("Your set needs a name. Even the Avengers named their team.");
    return;
  }

  const setTitleStep = document.getElementById("set-title-step");
  const categoryStep = document.getElementById("category-step");

  setTitleStep.classList.remove("current");
  setTitleStep.style.display = "none";

  categoryStep.style.display = "grid";
  categoryStep.classList.add("current");

  saveWizardState();
});

function bindWizardButtons() {
  const nextButton = document.querySelector("#next-category");
  nextButton.addEventListener("click", () => {
    const didSave = saveCurrentCategoryQuestions();
    if (!didSave) return;

    if (remainingCategories.length === 0) {
      nextButton.style.display = "none";
      document.querySelector("#submit-all").style.display = "inline-block";
      showError("Alright, Avengers… last step. Hit Submit when ready.");
      saveWizardState();
      return;
    }

    currentCategory = null;
    currentCategoryQuestions = [];

    const questionsStep = document.getElementById("questions-step");
    const categoryStep = document.getElementById("category-step");

    questionsStep.classList.remove("current");
    questionsStep.style.display = "none";

    categoryStep.style.display = "grid";
    categoryStep.classList.add("current");

    saveWizardState();
  });

  const submitButton = document.querySelector("#submit-all");
  submitButton.addEventListener("click", () => {
    const didSave = saveCurrentCategoryQuestions();
    if (!didSave) return;

    submitAllCategories();
  });
}

function saveCurrentCategoryQuestions() {
  clearError();
  syncDomToQuestions();

  const questionBoxes = document.querySelectorAll(".question-box");

  let isValid = true;
  const pointsSeen = new Set();

  questionBoxes.forEach((box, questionIndex) => {
    if (!isValid) return;

    const question = currentCategoryQuestions[questionIndex];
    const type = box.dataset.type;

    if (!question.text) {
      showError("Even Thor fills in all the fields. You should too.");
      isValid = false;
      return;
    }

    if (question.points == null) {
      showError("Pick points for every question. No Infinity Stones left behind.");
      isValid = false;
      return;
    }

    if (pointsSeen.has(question.points)) {
      showError("Duplicate points in one category? That's a Loki move. Pick different points.");
      isValid = false;
      return;
    }
    pointsSeen.add(question.points);

    if (type === "multipleChoice") {
      if (question.options.some(opt => !opt)) {
        showError("All multiple-choice options need text. Cap would be disappointed.");
        isValid = false;
        return;
      }
      if (question.correct == null) {
        showError("You gotta pick the correct answer. Even Doctor Strange chooses one timeline.");
        isValid = false;
        return;
      }
    }

    if (type === "trueFalse") {
      if (question.correct == null) {
        showError("Choose True or False for each one. No multiverse ambiguity.");
        isValid = false;
        return;
      }
    }

    if (type === "response") {
      if (!question.correct) {
        showError("Response questions need an answer. Don't ghost your own quiz.");
        isValid = false;
        return;
      }
    }
  });

  if (!isValid) return false;

  categoryQuestionMap[currentCategory] = [...currentCategoryQuestions];
  saveWizardState();
  return true;
}

async function submitAllCategories() {
  clearError();

  const setTitle = document.getElementById("set-title").value.trim();
  if (!setTitle) {
    showError("Name your set first. Assemble your thoughts.");
    return;
  }

  const allQuestions = [];
  categories.forEach(cat => {
    if (categoryQuestionMap[cat]) {
      allQuestions.push(...categoryQuestionMap[cat]);
    }
  });

  if (allQuestions.length !== 25) {
    showError("We need 25 questions total. The Avengers roster isn't full yet.");
    return;
  }

  const result = await sendAllQuestionsToBackend(setTitle, allQuestions);

  if (result) {
    clearWizardState();
    alert("All questions saved!");
  } else {
    showError("Something went wrong saving. Even Stark tech glitches sometimes.");
  }
}

async function sendAllQuestionsToBackend(setTitle, allQuestions) {
  try {
    const response = await fetch("../../index.php?command=save_question", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        title: setTitle,
        questions: allQuestions
      })
    });

    if (!response.ok) throw new Error("Failed to save questions");
    return await response.json();
  } catch (error) {
    console.error(error);
    return null;
  }
}

function showError(message) {
  const errorSpan = document.querySelector(".error-message");
  if (errorSpan) errorSpan.textContent = message;

  window.scrollTo({ top: 0, behavior: "smooth" });
}

function clearError() {
  const errorSpan = document.querySelector(".error-message");
  if (errorSpan) errorSpan.textContent = "";
}

function capitalize(word) {
  return word.charAt(0).toUpperCase() + word.slice(1);
}

renderCategoryButtons();
bindWizardButtons();
loadWizardState();
