const questionList = [];

function MultipleChoiceQuestion() {
  return {
    id: Date.now(),
    type: "multipleChoice",
    text: "",
    options: ["", "", "", ""],
    correct: null,
  };
}

function TrueFalseQuestion() {
  return {
    id: Date.now(),
    type: "trueFalse",
    text: "",
    correct: null,
  };
}

function ResponseQuestion() {
  return {
    id: Date.now(),
    type: "response",
    text: "",
    correct: "",
  };
}

function MultipleChoiceQuestionHTML(questionIndex) {
  const multipleChoiceQuestionHTML = `
    <div class="question-box" data-type="multipleChoice" data-question-index="${questionIndex}">
      <h2>Question ${questionIndex + 1}</h2>

      <label>Type:
        <select class="question-type" data-question-index="${questionIndex}">
          <option value="multipleChoice" selected>Multiple Choice</option>
          <option value="trueFalse">True or False</option>
          <option value="response">Response</option>
        </select>
      </label>

      <textarea class="question-text" placeholder="Enter question..."></textarea>
        <div>
          <input type="radio" name="correct-${questionIndex}" value="0">
          <input type="text" data-option-index="${questionIndex}-0" placeholder="Option 1" value="">
        </div>
        <div>
          <input type="radio" name="correct-${questionIndex}" value="1">
          <input type="text" data-option-index="${questionIndex}-1" placeholder="Option 2" value="">
        </div>
        <div>
          <input type="radio" name="correct-${questionIndex}" value="2">
          <input type="text" data-option-index="${questionIndex}-2" placeholder="Option 3" value="">
        </div>
        <div>
          <input type="radio" name="correct-${questionIndex}" value="3">
          <input type="text" data-option-index="${questionIndex}-3" placeholder="Option 4" value="">
        </div>

      <button class="remove-question" data-question-index="${questionIndex}">Remove</button>
    </div>
  `;
  return multipleChoiceQuestionHTML;
}

function TrueFalseQuestionHTML(questionIndex) {
  const trueFalseQuestionHTML = `
    <div class="question-box" data-type="trueFalse" data-question-index="${questionIndex}">
      <h2>Question ${questionIndex + 1}</h2>

      <label>Type:
        <select class="question-type" data-question-index="${questionIndex}">
          <option value="multipleChoice">Multiple Choice</option>
          <option value="trueFalse" selected>True or False</option>
          <option value="response">Response</option>
        </select>
      </label>

      <textarea class="question-text" placeholder="Enter question..."></textarea>

      <div>
        <label><input type="radio" name="tf-${questionIndex}" value="true"> True</label>
        <label><input type="radio" name="tf-${questionIndex}" value="false"> False</label>
      </div>
      
      <button class="remove-question" data-question-index="${questionIndex}">Remove</button>
    </div>
  `;
  return trueFalseQuestionHTML;
}

function ResponseQuestionHTML(questionIndex) {
  const responseQuestionHTML = `
    <div class="question-box" data-type="response" data-question-index="${questionIndex}">
      <h2>Question ${questionIndex + 1}</h2>

      <label>Type:
        <select class="question-type" data-question-index="${questionIndex}">
          <option value="multipleChoice">Multiple Choice</option>
          <option value="trueFalse">True or False</option>
          <option value="response" selected>Response</option>
        </select>
      </label>

      <textarea class="question-text" placeholder="Enter question..."></textarea>

      <input class="answer-input" type="text" placeholder="Correct response" value="">
      
      <button class="remove-question" data-question-index="${questionIndex}">Remove</button>
    </div>
  `;
  return responseQuestionHTML;
}


let questionListHTML = '';
let indexCount = 0;

function renderQuestionsList() {
  if (questionList.length === 0) {
    questionList.push(MultipleChoiceQuestion());
  }

  questionListHTML = '';
  indexCount = 0;

  questionList.forEach((question, questionIndex) => {
    indexCount = questionIndex;
    let questionHTML = '';

    if (question.type === "multipleChoice") {
      questionHTML = MultipleChoiceQuestionHTML(questionIndex);
    }
    if (question.type === "trueFalse") {
      questionHTML = TrueFalseQuestionHTML(questionIndex);
    }
    if (question.type === "response") {
      questionHTML = ResponseQuestionHTML(questionIndex);
    }
    questionListHTML += questionHTML;
  });

  const container = document.querySelector('#questions');
  container.innerHTML = questionListHTML;

  AddQuestionEvent();
  RemoveQuestionEvent();
  ChangeQuestionTypeEvent();
  SubmitQuestionsEvent();
}

function AddQuestionEvent() {
  const addQuestionButton = document.querySelector('#add-question');
  if (!addQuestionButton.dataset.bound) {
    addQuestionButton.addEventListener('click', () => {
      questionList.push(MultipleChoiceQuestion());
      renderQuestionsList();
    });
    addQuestionButton.dataset.bound = "1";
  }
}

function RemoveQuestionEvent() {
  const removeQuestionButtons = document.querySelectorAll('.remove-question');

  removeQuestionButtons.forEach((removeButton) => {
    removeButton.addEventListener('click', (event) => {
      const questionIndex = Number(event.currentTarget.dataset.questionIndex);
      questionList.splice(questionIndex, 1);
      renderQuestionsList();
    });
  });
}

function ChangeQuestionTypeEvent() {
  const dropdowns = document.querySelectorAll('.question-type');

  dropdowns.forEach((dropdown) => {
    dropdown.addEventListener('change', (event) => {
      const selectedType = event.target.value;
      const questionIndex = Number(event.target.dataset.questionIndex);

      if (selectedType === "multipleChoice") {
        questionList.splice(questionIndex, 1, MultipleChoiceQuestion());
      }
      if (selectedType === "trueFalse") {
        questionList.splice(questionIndex, 1, TrueFalseQuestion());
      }
      if (selectedType === "response") {
        questionList.splice(questionIndex, 1, ResponseQuestion());
      }
      renderQuestionsList();
    });
  });
}

function SubmitQuestionsEvent() {
  const submitQuestionButton = document.querySelector('#submit-questions');
  if (!submitQuestionButton.dataset.bound) {
    submitQuestionButton.addEventListener('click', () => {
      const isTitle = SaveTitle();
      if (!isTitle) return;

      const result = SaveAllQuestion();
      if (!result) return;

      Submit();
    });
    submitQuestionButton.dataset.bound = "1";
  }
}

function SaveTitle() {
  const setTitle = document.getElementById("set-title").value.trim();

  if (!setTitle) {
    document.querySelector(".error-message").textContent = "Please enter a title for your set.";
    return false;
  }
  return true;
}

function SaveAllQuestion() {
  const questionBoxes = document.querySelectorAll(".question-box");
  const errorSpan = document.querySelector(".error-message");

  errorSpan.textContent = "";
  questionList.length = 0;

  let isValid = true;

  questionBoxes.forEach((box) => {
    if (!isValid) return;

    const type = box.dataset.type;

    const textArea = box.querySelector(".question-text");
    if (!textArea || textArea.value.trim() === "") {
      errorSpan.textContent = "You must fill all the fields.";
      isValid = false;
      return;
    }

    if (type === "multipleChoice") {
      const question = MultipleChoiceQuestion();
      question.text = textArea.value.trim();

      const optionInputs = box.querySelectorAll("input[type='text']");

      optionInputs.forEach((input) => {
        if (!isValid) return;
        if (input.value.trim() === "") {
          errorSpan.textContent = "You must fill all the fields.";
          isValid = false;
          return;
        }
      });

      if (!isValid) return;

      optionInputs.forEach((input, index) => {
        question.options[index] = input.value.trim();
      });

      const selectedRadio = box.querySelector("input[type='radio']:checked");
      if (!selectedRadio) {
        errorSpan.textContent = "You must choose the correct answer.";
        isValid = false;
        return;
      }

      question.correct = Number(selectedRadio.value);

      questionList.push(question);
    }

    if (type === "trueFalse") {
      const question = TrueFalseQuestion();
      question.text = textArea.value.trim();

      const selectedRadio = box.querySelector("input[type='radio']:checked");
      if (!selectedRadio) {
        errorSpan.textContent = "You must choose true or false.";
        isValid = false;
        return;
      }

      question.correct = selectedRadio.value === "true";
      questionList.push(question);
    }

    if (type === "response") {
      const question = ResponseQuestion();
      question.text = textArea.value.trim();

      const answerInput = box.querySelector(".answer-input");
      if (!answerInput || answerInput.value.trim() === "") {
        errorSpan.textContent = "You must provide a response answer.";
        isValid = false;
        return;
      }

      question.correct = answerInput.value.trim();
      questionList.push(question);
    }
  });

  return isValid;
}


async function sendQuestionsToBackend() {
  try {
    const setTitle = document.getElementById("set-title").value.trim();

    if (!setTitle) {
      const errorSpan = document.querySelector(".error-message");
      errorSpan.textContent = "Please enter a title for your question set.";
      return null;
    }

    const response = await fetch("../../index.php?command=save_question", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        title: setTitle,
        questions: questionList
      })
    });

    if (!response.ok) {
      throw new Error("Failed to save questions");
    }

    const data = await response.json();
    console.log("Saved successfully:", data);

    return data;
  } catch (error) {
    console.error("Error saving questions:", error);
    return null;
  }
}

async function Submit() {
  const result = await sendQuestionsToBackend();

  if (result) {
    alert("Questions saved!");
  } else {
    //window.location.href = "../../index.php?command=home";
  }
}

renderQuestionsList();
