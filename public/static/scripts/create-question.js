const questions = [];

function newQuestion() {
  return {
    id: Date.now(),
    type: "Multiple Choice",
    text: "",
    options: ["", "", "", ""],
    correct: null,
    trueFalseAnswer: null,
    responseAnswer: ""
  };
}

function render() {
  const container = document.querySelector('#questions');
  container.innerHTML = questions.map((question, questionIndex) => `
    <div class="question-box">
      <h2>Question ${questionIndex + 1}</h2>

      <label>Type:
        <select data-type-index="${questionIndex}">
          <option ${question.type === "Multiple Choice" ? "selected" : ""}>Multiple Choice</option>
          <option ${question.type === "True or False" ? "selected" : ""}>True or False</option>
          <option ${question.type === "Response" ? "selected" : ""}>Response</option>
        </select>
      </label>

      <textarea data-text-index="${questionIndex}" placeholder="Enter question...">${question.text}</textarea>

      ${question.type === "Multiple Choice" ? `
        ${question.options.map((optionText, optionIndex) => `
          <div>
            <input type="radio" name="correct-${questionIndex}" data-correct-index="${questionIndex}" value="${optionIndex}" ${question.correct === optionIndex ? "checked" : ""}>
            <input type="text" data-option-index="${questionIndex}-${optionIndex}" placeholder="Option ${optionIndex + 1}" value="${optionText}">
          </div>`).join('')}
      ` : question.type === "True or False" ? `
        <div>
          <label><input type="radio" name="tf-${questionIndex}" data-tf-index="${questionIndex}" value="true" ${question.trueFalseAnswer === true ? "checked" : ""}> True</label>
          <label><input type="radio" name="tf-${questionIndex}" data-tf-index="${questionIndex}" value="false" ${question.trueFalseAnswer === false ? "checked" : ""}> False</label>
        </div>
      ` : `
        <input type="text" data-response-index="${questionIndex}" placeholder="Correct response" value="${question.responseAnswer}">
      `}

      <button data-remove-index="${questionIndex}">Remove</button>
    </div>
  `).join('');
}

document.addEventListener('change', event => {
  if (event.target.dataset.typeIndex !== undefined) {
    const questionIndex = Number(event.target.dataset.typeIndex);
    questions[questionIndex].type = event.target.value;
    render();
  }
  if (event.target.dataset.textIndex !== undefined) {
    const questionIndex = Number(event.target.dataset.textIndex);
    questions[questionIndex].text = event.target.value;
  }
  if (event.target.dataset.optionIndex) {
    const [questionIndex, optionIndex] = event.target.dataset.optionIndex.split('-').map(Number);
    questions[questionIndex].options[optionIndex] = event.target.value;
  }
  if (event.target.dataset.correctIndex !== undefined) {
    const questionIndex = Number(event.target.dataset.correctIndex);
    questions[questionIndex].correct = Number(event.target.value);
  }
  if (event.target.dataset.tfIndex !== undefined) {
    const questionIndex = Number(event.target.dataset.tfIndex);
    questions[questionIndex].trueFalseAnswer = event.target.value === "true";
  }
  if (event.target.dataset.responseIndex !== undefined) {
    const questionIndex = Number(event.target.dataset.responseIndex);
    questions[questionIndex].responseAnswer = event.target.value;
  }
});

document.addEventListener('click', event => {
  if (event.target.dataset.removeIndex !== undefined) {
    const questionIndex = Number(event.target.dataset.removeIndex);
    questions.splice(questionIndex, 1);
    render();
  }
});

document.querySelector('#add-question').addEventListener('click', () => {
  questions.push(newQuestion());
  render();
});

document.querySelector('#submit-questions').addEventListener('click', () => {
  console.log(questions);
  alert("Check console for question data");
});

questions.push(newQuestion());
render();

document.querySelector('#submit-questions').addEventListener('click', async () => {
  const titleEl = document.getElementById('set-title');
  const title = (titleEl?.value || '').trim();

  if (!title) {
    alert('Please enter a set title.');
    return;
  }

  //question field validation
  for (let i = 0; i < questions.length; i++) {
    const q = questions[i];
    if (!q.text.trim()) { alert(`Question ${i + 1}: enter the question text.`); return; }

    if (q.type === 'Multiple Choice') {
      for (let j = 0; j < q.options.length; j++) {
        if (!q.options[j].trim()) { alert(`Question ${i + 1}: Option ${j + 1} is empty.`); return; }
      }
      if (q.correct === null) { alert(`Question ${i + 1}: select a correct option.`); return; }
    }
    if (q.type === 'True or False' && q.trueFalseAnswer === null) {
      alert(`Question ${i + 1}: choose True or False.`); return;
    }
    if (q.type === 'Response' && !q.responseAnswer.trim()) {
      alert(`Question ${i + 1}: fill the correct response.`); return;
    }
  }

  // Build payload for API
  const payload = {
    title: title,
    questions: questions.map(q => {
      if (q.type === "Multiple Choice") {
        return { type: q.type, prompt: q.text, options: q.options, correctIndex: q.correct };
      } else if (q.type === "True or False") {
        return { type: q.type, prompt: q.text, correctBool: q.trueFalseAnswer };
      } else {
        return { type: q.type, prompt: q.text, correctText: q.responseAnswer };
      }
    })
  };

  try {
    const res = await fetch('api/questions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    // parse JSON if it is JSON 
    if (!res.ok) throw new Error(await res.text());
    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) throw new Error(await res.text());
    const data = await res.json();

    if (data.ok) {
      alert(`Saved set #${data.set_id} with ${data.inserted} question(s).`);
      window.location.href = `index.php?command=sets&id=${data.set_id}`;
    } else {
      alert('Error: ' + (data.error || 'Unknown'));
    }
  } catch (err) {
    console.error(err);
    alert('Network error.');
  }

});
