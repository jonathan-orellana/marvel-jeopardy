//// JAVASCRIPT OBJECTS /////

// Data structure 
const questionList = [];

// Questions Objects
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
//// HTML GENERATOR /////

// Questions Objects HTML
function MultipleChoiceQuestionHTML(questionIndex) {
  const multipleChoiceQuestionHTML = `
    <div class="question-box" data-type="multipleChoice">
      <h2>Question ${questionIndex + 1}</h2>

      <label>Type:
        <select class="question-type">
          <option value="multipleChoice">Multiple Choice</option>
          <option value="trueFalse"selected="">True or False</option>
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

      <button class="remove-question" data-question-index=${questionIndex}>Remove</button>
    </div>
  `;

  return multipleChoiceQuestionHTML;
}

function TrueFalseQuestionHTML(questionIndex) {
  const trueFalseQuestionHTML = `
    <div class="question-box" data-type="trueFalse">
      <h2>Question ${questionIndex + 1}</h2>

      <label>Type:
        <select class="question-type" data-type-index="0">
          <option value="multipleChoice">Multiple Choice</option>
          <option value="trueFalse"selected="">True or False</option>
          <option value="response">Response</option>
        </select>
      </label>

      <textarea data-text-index="0" placeholder="Enter question..."></textarea>

        <div>
          <label><input type="radio" name="tf-0" data-tf-index="0" value="true"> True</label>
          <label><input type="radio" name="tf-0" data-tf-index="0" value="false"> False</label>
        </div>
      
      <button class="remove-question" data-question-index=${questionIndex}>Remove</button>
    </div>
  `;

  return trueFalseQuestionHTML;
}

function ResponseQuestionHTML(questionIndex) {
  const responseQuestionHTML = `
    <div class="question-box" data-type="response">
      <h2>Question ${questionIndex + 1}</h2>

      <label>Type:
        <select class="question-type" data-type-index="0">
          <option value="multipleChoice">Multiple Choice</option>
          <option value="trueFalse"selected="">True or False</option>
          <option value="response">Response</option>
        </select>
      </label>

      <textarea data-text-index="0" placeholder="Enter question..."></textarea>

        <input class="answer-input" type="text" data-response-index="0" placeholder="Correct response" value="">
      
      <button class="remove-question" data-question-index=${questionIndex}>Remove</button>
    </div>
  `;

  return responseQuestionHTML;
}

//// RENDER /////

// Render
let questionListHTML = '';
let indexCount = 0;

function renderQuestionsList() {
  if (questionList.length === 0) {
    questionListHTML += MultipleChoiceQuestionHTML(indexCount);
  }

  questionList.forEach(question => {
    indexCount++;
    const questionHTML = '';

    if (question.type = "multipleChoice") {
      questionHTML = MultipleChoiceQuestionHTML(indexCount);
    }
    if (question.type = "trueFalse") {
      questionHTML = TrueFalseQuestionHTML(indexCount);
    }
    if (question.type = "response") {
      questionHTML = ResponseQuestionHTML(indexCount);
    }

    questionListHTML += questionHTML;
  });

  // Render to the section
  const container = document.querySelector('#questions');
  container.innerHTML = questionListHTML;

  // Add events
  AddQuestionEvent();
  RemoveQuestionEvent();
  ChangeQuestionTypeEvent();
  SubmitQuestionsEvent();
}

//// EVENT LISTENER /////

// Add question event
function AddQuestionEvent() {
  const addQuestionButton = document.querySelector('#add-question');

  addQuestionButton.addEventListener('click', () => {
    questionList.push(MultipleChoiceQuestion);

  });
  renderQuestionsList();
}

// Remove question event
function RemoveQuestionEvent() {
  const removeQuestionButton = document.querySelectorAll('.remove-question');

  removeQuestionButton.forEach( button => {
    button.addEventListener('click', () => {
      const questionIndex = button.target.dataset.questionIndex;

      questionList.splice(questionIndex, 1 );;
    });
  });
  renderQuestionsList();
}

// Question type event 
function ChangeQuestionTypeEvent() {
  const dropDown = document.querySelectorAll('.question-type');

  dropDown.addEventListener('change', option => {
    const value = option.target.value;

    if (value === "multipleChoice") {
      questionList.splice(indexCount, 1, MultipleChoiceQuestion());
    }
    if (value === "trueFalse") {
      questionList.splice(indexCount, 1, TrueFalseQuestion());
    }
    if (value === "response") {
      questionList.splice(indexCount, 1, ResponseQuestion());
    }
  });
  renderQuestionsList();
}

// Submit event
function SubmitQuestionsEvent() {
  const submitQuestionButton = document.querySelector('#submit-questions');

  submitQuestionButton.addEventListener('click', () => {
    SaveAllQuestion();
  })
  window.location.href = "../../index.php?command=home";
}

// Helpers 
function SaveAllQuestion() {
  questionList.length = 0;
  const questionBoxes = document.querySelectorAll(".question-box");

  questionBoxes.forEach((box) => {
    const questionType = box.target.dataset.type;

    if (questionType == 'multipleChoice') {
      const question  = MultipleChoiceQuestion();

      const textArea = box.querySelector(".question-text");
      question.text = textArea.value;

      const optionInputs = box.querySelectorAll("input[type='text']");
      optionInputs.forEach((input, index) => {
        question.options[index] = input.value;
      });

      const selectedRadio = box.querySelector("input[type='radio']:checked");
      if (selectedRadio) {
        question.correct = Number(selectedRadio.value);
      } else {
        question.correct = null; // none selected
      }
      questionList.push(question);
    }
    
    if (questionType == 'trueFalse') {
      const question  = TrueFalseQuestion();

      const textArea = box.querySelector(".question-text");
      question.text = textArea.value;

      const selectedRadio = box.querySelector("input[type='radio']:checked");
      if (selectedRadio) {
        question.correct = Number(selectedRadio.value);
      } else {
        question.correct = null; // none selected
      }
      questionList.push(question);
    }
      
    if (questionType == 'response') {
      const question  = ResponseQuestion();

      const textArea = box.querySelector(".question-text");
      question.text = textArea.value;

      const answer = box.querySelector(".answer-input");
      question.correct = answer.value;
      questionList.push(question);
    }
  });
}


