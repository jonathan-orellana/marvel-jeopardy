
function buildMultipleChoiceAnswerHTML(questionId) {
  const optionIndices = [0, 1, 2, 3];

  const optionsHTML = optionIndices.map((optionIndex) => `
    <div class="mc-option">
      <input type="radio" name="correctIndex-${questionId}" value="${optionIndex}">
      <span>Option ${optionIndex + 1}:</span>
      <input type="text" class="input-option mc-opt" value="">
    </div>
  `).join("");

  return `
    <div class="section mc-section">
      ${optionsHTML}
    </div>
  `;
}

function buildTrueFalseAnswerHTML(questionId) {
  return `
    <div class="section tf-section">
      <label><input type="radio" name="correctBool-${questionId}" value="1"> True</label>
      <label><input type="radio" name="correctBool-${questionId}" value="0"> False</label>
    </div>
  `;
}

function buildResponseAnswerHTML() {
  return `
    <div class="section resp-section">
      <label>
        Correct answer:
        <input type="text" class="input-full resp-text" value="">
      </label>
    </div>
  `;
}

function renderAnswerAreaForType(answerAreaEl, questionType, questionId) {
  if (questionType === "multipleChoice") {
    answerAreaEl.innerHTML = buildMultipleChoiceAnswerHTML(questionId);
    return;
  }

  if (questionType === "trueFalse") {
    answerAreaEl.innerHTML = buildTrueFalseAnswerHTML(questionId);
    return;
  }

  answerAreaEl.innerHTML = buildResponseAnswerHTML();
}

function bindTypeDropdown(questionItemEl) {
  const questionId = questionItemEl.dataset.id;
  const typeSelectEl = questionItemEl.querySelector(".q-type");
  const answerAreaEl = questionItemEl.querySelector(".answer-area");

  typeSelectEl.addEventListener("change", () => {
    const selectedType = typeSelectEl.value;
    renderAnswerAreaForType(answerAreaEl, selectedType, questionId);
  });
}

function getSelectedRadioValue(containerEl) {
  const checked = containerEl.querySelector("input[type='radio']:checked");
  return checked ? checked.value : null;
}

function collectMultipleChoiceData(questionItemEl) {
  const options = Array.from(questionItemEl.querySelectorAll(".mc-opt"))
    .map(input => input.value.trim());

  const selectedIndex = getSelectedRadioValue(questionItemEl);

  return {
    options,
    correct_index: selectedIndex !== null ? Number(selectedIndex) : null
  };
}

function collectTrueFalseData(questionItemEl) {
  const selected = getSelectedRadioValue(questionItemEl);
  return {
    is_true: selected !== null ? selected === "1" : null
  };
}

function collectResponseData(questionItemEl) {
  const answerText = questionItemEl.querySelector(".resp-text")?.value.trim() ?? "";
  return { answer_text: answerText };
}

function collectQuestionUpdate(questionItemEl) {
  const questionId = Number(questionItemEl.dataset.id);
  const questionType = questionItemEl.querySelector(".q-type").value;
  const questionText = questionItemEl.querySelector(".q-prompt").value.trim();

  const update = {
    id: questionId,
    question_type: questionType,
    text: questionText
  };

  if (questionType === "multipleChoice") {
    const mc = collectMultipleChoiceData(questionItemEl);
    update.options = mc.options;
    update.correct_index = mc.correct_index;
    return update;
  }

  if (questionType === "trueFalse") {
    const tf = collectTrueFalseData(questionItemEl);
    update.is_true = tf.is_true;
    return update;
  }

  const resp = collectResponseData(questionItemEl);
  update.answer_text = resp.answer_text;
  return update;
}

function collectAllQuestionUpdates() {
  return Array.from(document.querySelectorAll(".question-item"))
    .map(collectQuestionUpdate);
}

async function saveAllQuestionsToBackend(questionUpdates) {
  const response = await fetch("index.php?command=update_questions", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ questions: questionUpdates })
  });

  return response.json();
}

function bindSaveAllButton() {
  const saveAllButton = document.getElementById("save-all");

  saveAllButton.addEventListener("click", async () => {
    const questionUpdates = collectAllQuestionUpdates();

    try {
      const result = await saveAllQuestionsToBackend(questionUpdates);
      if (result.ok) alert("Saved!");
      else alert((result.errors || ["Save failed"]).join("\n"));
    } catch (err) {
      console.error(err);
      alert("Save failed");
    }
  });
}

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".question-item").forEach(bindTypeDropdown);
  bindSaveAllButton();
});
