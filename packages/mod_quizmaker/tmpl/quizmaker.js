/**
 * Collects necessary elements from the DOM for the quiz and initializes some variables.
 * @constant {object} quizData - The quiz data object.
 * @constant {HTMLElement} quizForm - The form element containing the quiz questions.
 * @constant {HTMLElement} startSection - The section containing the start button and text.
 * @constant {array} questions - An array of the question list items in the quiz form.
 * @constant {array} choices - An array of the answer choice list items in the quiz form.
 * @constant {HTMLElement} scoreElement - The element displaying the current score.
 * @constant {HTMLElement} nextButton - The button used to navigate to the next question.
 * @constant {HTMLElement} startButton - The button used to start the quiz.
 * @constant {HTMLElement} submitButton - The button used to submit the form with quiz data.
 * @constant {HTMLElement} topBar - The top bar containing the quiz title and current score.
 * @constant {HTMLElement} progressBar - The progress bar element.
 * @constant {HTMLElement} progressBarContainer - The container for the progress bar.
 * @constant {HTMLElement} progressText - The text indicating the current progress.
 * @constant {HTMLElement} checkbox - The optin checkbox.
 * @constant {HTMLElement} email - The input type email element.
 * @constant {HTMLElement} spinner - The spinner element displayed while loading quiz data.
 * @constant {array} quizQuestions - An array of objects containing the quiz questions and answers.
 * @let {number} correctAnswers - The number of questions the user answered correctly.
 * @let {number} currentQuestionIndex - The index of the current question in the quizQuestions array.
 * @let {boolean} acceptingAnswers - A flag indicating whether answers are currently being accepted.
 * @let {object|null} selectedAnswer - The answer currently selected by the user.
 * @let {number} score - The user's current score.
 * @let {object} response_data - The response data object.
 * @let {object} isFinal - If the final screen is displayed.
 * @constant {number} totalQuestions - The total number of questions in the quiz.
 **/

const quizData = data;
const quizForm = document.getElementById("qz-quiz-form");
const startSection = document.querySelector(".qz-start");
const questions = Array.from(document.querySelectorAll(".qz-question-list"));
const choices = Array.from(document.querySelectorAll(".qz-choice-text"));
const scoreElement = document.getElementById("qz-score");
const nextButton = document.getElementById("qz-next-button");
const submitButton = document.querySelector("button[type='submit']");
const startButton = document.getElementById("qz-start-button");
const topBar = document.querySelector(".qz-top-bar-score");
const progressBar = document.querySelector(".qz-progress");
const progressBarContainer = document.querySelector(".qz-progress-bar");
const progressText = document.querySelector(".qz-progress-text");
const checkbox = document.getElementById("qz-email-optin");
const email = document.querySelector("#qz-email");;
const spinner = document.getElementById("qz-spinner");

let correctAnswers = 0;
let currentQuestionIndex = 0;
let acceptingAnswers = false;
let selectedAnswer = null;
let score = 0;
let response_data = {};
let userAnswers = {};
let isFinal;

hideQuestions();
disableNextButton();

/**
 * Maps the quiz data to an array of questions and answers
 * @constant mapQuizDataToQuestions
 * @param {Object} quizData - The quiz data to map
 * @returns {Array} An array of questions and answers
 */
const mappedQuizQuestions = Object.values(quizData).map((question) => {
  return {
    question: question.question,
    answers: Object.values(question.answers).map((answer) => {
      return {
        answer: answer.answer,
        correct: !!answer.correct,
      };
    }),
  };
});

const totalQuestions = mappedQuizQuestions.length;

startButton.addEventListener("click", () => {
  if (quizData?.length) {
    startQuiz();
  }
});

/**
 * Starts the quiz when there is quiz data available.
 * @function startQuiz
 */
function startQuiz() {
  acceptingAnswers = true;

  showNextButton();
  getNextQuestion();
  showTopBar();
  showQuestions();
  updateScoreDisplay();
  scrollIntoViewIfNeeded(document.querySelector(".qz-questions"));
}

/**
 * Shows the 'Next' button and hides the start button.
 */
function showNextButton() {
  startSection.remove();
  startButton.classList.add("qz-hidden");
  nextButton.classList.remove("qz-hidden");
  nextButton.parentElement.parentElement.classList.remove("qz-hidden");
}

/**
 * Shows the top bar with the quiz progress information.
 * @function showTopBar
 */
function showTopBar() {
  topBar.classList.remove("qz-hidden");
  updateProgressBar();
}

/**
 * Retrieves the next question in the quiz.
 * @function getNextQuestion
 */
function getNextQuestion() {
  const currentQuestion = questions[currentQuestionIndex];
  if (!currentQuestion) {
    displayFinalScore();
    return;
  }
  if (!currentQuestion.answers || currentQuestion.answers.length === 0) return;
}

/**
 * Enables click handling of quiz answer choices.
 * @function enableHandleChoiceClick
 * @param {Event} event - The click event.
 */
function enableHandleChoiceClick(event) {
  handleAnswerChoiceClick(event.target);
}

/**
 * Enables keydown handling of quiz answer choices.
 * @function enableHandleChoiceKeyDown
 * @param {Event} event - The keydown event.
 */
function enableHandleChoiceKeyDown(event) {
  if (event.key === "Enter") {
    const selectedElement = event.target.querySelector(".qz-choice-text");
    handleAnswerChoiceClick(selectedElement);
  }
}

/**
 * Updates the score display with the current quiz score.
 * @function updateScoreDisplay
 */
function updateScoreDisplay() {
  score = (correctAnswers / mappedQuizQuestions.length) * 100;
  scoreElement.textContent = `${parseInt(score)}`;
}

/**
 * Hides all quiz questions.
 * @function hideQuestions
 */
function hideQuestions() {
  questions.slice(0).forEach((question) => {
    question.classList.add("qz-hidden");
  });
}
/**
 * Submits the form data to the server for processing.
 * @function submitForm
 * @returns {Promise} - A promise that resolves with the server response
 * data.
 */
async function submitForm(event) {
  event.preventDefault();
  // const optin = document.querySelector("#optin").checked;
  const formData = new FormData();
  const emailValue = email.value;
  const score = document.querySelector("#qz-score-input").value;
  const params = document.querySelectorAll(".qz-field-questions");
  const answersObj = {};

  params.forEach((question, index) => {
    const name = `Vraag ${index + 1}`;
    const value = question.value;
    answersObj[name] = value;
  });

  formData.append("answers", JSON.stringify(answersObj));
  formData.append("email", emailValue);
  formData.append("score", score);

  const url =
    "/index.php?option=com_ajax&module=quizmaker&format=json&method=postQuizData";

  const data = await submitData(url, formData);
  if (data) {
    showSuccess(data);
  } else {
    showError(data);
  }
}

/**
 * Submits the quiz data to the server for processing.
 * @function submitForm
 * @returns {Promise} - A promise that resolves with the server response
 * data.
 */
async function validateAnswer(requestQuizData) {
  const url = "/index.php?option=com_ajax&module=quizmaker&format=json";
  await submitData(url, requestQuizData);
}

/**
 * Submits the data to the server.
 * @function submitData
 * @param {Object} data - data for the post request
 * information.
 * @returns {Promise} - A promise that resolves with the server response
 * data.
 */
async function submitData(url, data) {
  let params = new URLSearchParams();
  let queryString;

  if (
    data instanceof FormData &&
    data.has("email") &&
    data.has("score") &&
    data.has("answers")
  ) {
    queryString = new URLSearchParams(data);
  } else {
    if (typeof data?.currentQuestionIndex === "number") {
      params.append("questionIndex", data.currentQuestionIndex);
    }

    if (typeof data?.choiceIndex) {
      params.append("choiceIndex", data.choiceIndex);
    }

    if (typeof data?.score === "number") {
      params.append("score", data.score);
    }
    queryString = params.toString();
  }

  return fetch(url, {
    method: "POST",
    headers: {
      "Content-type": "application/x-www-form-urlencoded",
    },
    body: queryString,
  })
    .then((response) => {
      if (response.ok) {
        return response.text();
      } else {
        throw new Error("Error:", response.statusText);
      }
    })
    .then((data) => {
      const responseObjects = data.split("}{"); // json split door format response vanuit backend

      responseObjects.forEach((responseJson, index) => {
        try {
          const responseObject = JSON.parse(
            (index === 0 ? "" : "{") +
              responseJson +
              (index === responseObjects.length - 1 ? "" : "}")
          );

          if (responseObject.data) {
            response_data = responseObject.data;
          }
        } catch (e) {
          console.log("Error parsing JSON:", e);
        }
      });
      return response_data;
    })
    .catch((error) => {
      console.log("Error:", error);
    });
}

/**
 * Toggles the visibility of a spinner element.
 * @function showSpinner
 * @param {boolean} show - Indicates whether the spinner should be shown
 * or hidden.
 */
function showSpinner(show) {
  if (show) {
    document.querySelector(".qz-next").classList.add("qz-spinner");
    spinner.classList.remove("qz-hidden");
  } else {
    document.querySelector(".qz-next").classList.remove("qz-spinner");
    spinner.classList.add("qz-hidden");
  }
}

/**
 * Handles a user clicking an answer choice and submits the user's choice to the server.
 *
 * @function handleAnswerChoiceClick
 * @param {HTMLElement} choice - The HTML element representing the user's selected answer choice.
 * @returns {Promise<void>} - A promise that resolves when the server response has been handled.
 */
async function handleAnswerChoiceClick(choice) {
  if (!acceptingAnswers) return;
  acceptingAnswers = false;
  showSpinner(true);

  enableAnswerSelection();
  nextButton.addEventListener("click", handleNextQuestionClick);

  const { choiceIndex } = choice.dataset;

  const requestQuizData = {
    currentQuestionIndex,
    choiceIndex,
    choice,
    score,
  };

  const response = await validateAnswer(requestQuizData);
  handleResponse(response, choice, choiceIndex);

  showSpinner(false);
}

/**
 * Handles the response from the server after the user has submitted an answer choice.
 *
 * @function handleResponse
 * @param {object} response - The response from the server.
 * @param {HTMLElement} choice - The HTML element representing the user's selected answer choice.
 * @param {string} choiceIndex - The index of the user's selected answer choice.
 */
function handleResponse(response, choice, choiceIndex) {
  const { question, answers } = mappedQuizQuestions[currentQuestionIndex];

  if (Object.keys(response_data).length > 0) {
    const { isCorrect, correctAnswer } = response_data;

    selectedAnswer = answers[choiceIndex];

    const rightAnswer = choice
      .closest(".qz-question-container")
      .querySelector("#qz-right-answer-context");
    const wrongAnswer = choice
      .closest(".qz-question-container")
      .querySelector("#qz-wrong-answer-context");

    const choiceContainer = choice.closest(".qz-choice-container");

    resetChoiceClasses(choiceContainer);

    if (isCorrect) {
      handleCorrectAnswer(rightAnswer, choiceContainer, choice);
    } else {
      handleIncorrectAnswer(
        wrongAnswer,
        choiceContainer,
        choice,
        correctAnswer
      );
    }

    userAnswers[currentQuestionIndex] = {
      question,
      selectedAnswer,
    };

    if (currentQuestionIndex <= totalQuestions - 1) {
      updateScoreDisplay();
    }

    scrollIntoViewIfNeeded(
      document.querySelectorAll(".qz-question-container")[currentQuestionIndex]
    );
  }
}

/**
 * Handles the case where the user's answer choice was correct.
 *
 * @function handleCorrectAnswer
 * @param {HTMLElement} rightAnswer - The HTML element containing the correct answer information.
 * @param {HTMLElement} choiceContainer - The HTML element containing the user's answer choice.
 * @param {HTMLElement} choice - The HTML element representing the user's selected answer choice.
 */
function handleCorrectAnswer(rightAnswer, choiceContainer, choice) {
  const { explanation } = response_data;

  rightAnswer.textContent += explanation;
  rightAnswer.classList.remove("qz-hidden");
  choiceContainer.classList.add("qz-correct");

  correctAnswers++;
  updateScoreDisplay();

  choice.innerHTML += `<span class="qz-emoji"> 🎉</span>`;
}

/**
 * Handles the case where the user's answer choice was incorrect.
 *
 * @function handleIncorrectAnswer
 * @param {HTMLElement} wrongAnswer - The HTML element containing the incorrect answer information.
 * @param {HTMLElement} choiceContainer - The HTML element containing the user's answer choice.
 * @param {HTMLElement} choice - The HTML element representing the user's selected answer choice.
 * @param {number} correctAnswer - The index of the correct answer choice.
 */
function handleIncorrectAnswer(
  wrongAnswer,
  choiceContainer,
  choice,
  correctAnswer
) {
  const { explanation } = response_data;

  wrongAnswer.textContent += explanation;
  wrongAnswer.classList.remove("qz-hidden");
  choiceContainer.classList.add("qz-incorrect");

  choiceContainer.parentElement
    .querySelectorAll(".qz-choice-container")
    .forEach((c) => {
      if (c !== choiceContainer) {
        c.classList.add("qz-incorrect-light");
      }
    });

  const correctChoiceContainer = choiceContainer.parentElement
    .querySelector(`[data-choice-index="${correctAnswer}"]`)
    .closest(".qz-choice-container");

  correctChoiceContainer.classList.add("qz-correct-light");
  correctChoiceContainer.classList.remove("qz-incorrect-light");
}

/**
 * Handles a user clicking the "Next" button to move to the next question.
 *
 * @function handleNextQuestionClick
 */
function handleNextQuestionClick() {
  if (!selectedAnswer && currentQuestionIndex > 0) {
    alert("Selecteer eerst een optie");
    return;
  } else if (!selectedAnswer) {
    return;
  }

  disableNextButton();
  hideCurrentQuestion();
  goToNextQuestion();
}

/**
 * Hides the current question and shows the next question.
 *
 * @function hideCurrentQuestion
 */
function hideCurrentQuestion() {
  const currentQuestionElement = questions[currentQuestionIndex];
  currentQuestionElement.classList.add("qz-hidden");
  currentQuestionIndex++;
  updateProgressBar();
}

/**
 * Goes to the next question in the quiz.
 *
 * @function goToNextQuestion
 */
function goToNextQuestion() {
  const nextQuestionElement = questions[currentQuestionIndex];

  if (!nextQuestionElement) {
    getNextQuestion();
    disableNextButton();
    return;
  }

  const questionContainer = getQuestionContainer();
  scrollIntoViewIfNeeded(questionContainer);

  showNextQuestion(nextQuestionElement);
  acceptingAnswers = true;
}

/**
 * Returns the DOM element that contains the current question.
 *
 * @function qetQuestionContainer
 * @returns {HTMLElement} The DOM element that contains the current question.
 */
function getQuestionContainer() {
  return document.querySelectorAll(".qz-question-container")[
    currentQuestionIndex
  ];
}

/**
 * Scrolls the given element into view, if it is not already in view.
 *
 * @function scrollIntoViewIfNeeded
 * @param {HTMLElement} element - The element to scroll into view.
 */
function scrollIntoViewIfNeeded(element) {
  setTimeout(() => {
    element.scrollIntoViewIfNeeded({ block: "end" });
  }, 300);
}

/**
 * Shows the next question in the quiz.
 *
 * @function showNextQuestion
 * @param {HTMLElement} nextQuestionElement - The DOM element that contains the next question.
 */
function showNextQuestion(nextQuestionElement) {
  nextQuestionElement.classList.remove("qz-hidden");
  resetAnswerState(nextQuestionElement);
}

/**
 * Resets the answer state for the given question.
 *
 * @function resetAnswerState
 * @param {HTMLElement} nextQuestionElement - The DOM element that contains the question to reset.
 */
function resetAnswerState(nextQuestionElement) {
  const rightAnswerContext = nextQuestionElement.querySelector(
    "#qz-right-answer-context"
  );
  const wrongAnswerContext = nextQuestionElement.querySelector(
    "#qz-wrong-answer-context"
  );
  rightAnswerContext.classList.add("qz-hidden");
  wrongAnswerContext.classList.add("qz-hidden");
  resetChoiceClasses(nextQuestionElement);
}

/**
 * Resets the classes of the choice containers for the given question.
 *
 * @function resetChoiceClasses
 * @param {HTMLElement} questionElement - The DOM element that contains the question to reset.
 */
function resetChoiceClasses(questionElement) {
  questionElement.querySelectorAll(".qz-choice-container").forEach((c) => {
    c.classList.remove(
      "qz-correct",
      "qz-incorrect",
      "qz-correct-light",
      "qz-incorrect-light"
    );
  });
}

/**
 * Enables answer selection for the current question.
 *
 * @function enableAnswerSelection
 */
function enableAnswerSelection() {
  nextButton.disabled = false;
  selectedAnswer = null;
}

/**
 * Disables the "Next" button.
 *
 * @function disableNextButton
 */
function disableNextButton() {
  nextButton.disabled = true;
}

/**
 * Enables the "Next" button.
 *
 * @function enableNextButton
 */
function enableNextButton() {
  nextButton.disabled = false;
}

/**
 * Shows the questions in the quiz.
 *
 * @function showQuestions
 */
function showQuestions() {
  const currentQuestionElement = questions[currentQuestionIndex];
  currentQuestionElement.classList.remove("qz-hidden");
  document.querySelector(".qz-score-section").classList.remove("qz-hidden");
}

/**
 * Updates the progress bar and progress label.
 *
 * @function updateProgressBar
 */
function updateProgressBar() {
  const percentage = (currentQuestionIndex / totalQuestions) * 100;
  const progressLabel =
    currentQuestionIndex !== totalQuestions
      ? `${currentQuestionIndex + 1} / ${totalQuestions}`
      : `${currentQuestionIndex} / ${totalQuestions}`;
  progressText.textContent = progressLabel;
  progressBar.style.width = `${percentage}%`;
}

/**
 * Display the final score and feedback to the user.
 *
 * @function displayFinalScore
 */
async function displayFinalScore() {
  isFinal = true;
  const submissionForm = document.getElementById("esf");
  submissionForm.style.display = "flex";

  nextButton.remove();

  const inputScore = document.querySelector(`[name="score"]`);
  inputScore.value = score;
  document.querySelector(".qz-score-container").classList.add("qz-final");
  document.getElementById("qz-form-quiz").classList.add("qz-final");

  const requestQuizData = {
    currentQuestionIndex,
    score,
  };

  await validateAnswer(requestQuizData);
  const finalScoreResult = document.createElement("div");
  finalScoreResult.classList.add("qz-final-container");
  const finalScoreResultTitle = document.createElement("h3");
  finalScoreResultTitle.classList.add("qz-explanation");
  finalScoreResultTitle.classList.add("qz-question-text");
  finalScoreResultTitle.innerHTML = response_data.finalScoreTitle;

  const finalScoreResultSubtitle = document.createElement("p");
  finalScoreResultSubtitle.classList.add("qz-subtitle");
  finalScoreResultSubtitle.innerHTML = response_data.finalScoreSubtitle;

  document
    .querySelector(".qz-score.qz-section-container")
    .classList.add("qz-final");
  document.querySelector(".qz-top-bar-score").classList.add("qz-final");
  document
    .querySelector(".qz-score.qz-section-container")
    .classList.remove("qz-questions");
  document.querySelector(".qz-score-wrapper").classList.add("qz-final");
  document.querySelector(".qz-score-container").append(finalScoreResult);
  finalScoreResult.append(finalScoreResultTitle);
  finalScoreResult.append(finalScoreResultSubtitle);

  userAnswers.score = score;
  userAnswers.complete = true;
  sessionStorage.setItem("answers", JSON.stringify(userAnswers));
  setParamValues(userAnswers);
}

/**
 * Set the parameter values for the submitted quiz data.
 * @function setParamValues
 * @param {Object} answers - The submitted quiz data.
 */
function setParamValues(answers) {
  const submittedQuizData = answers;

  const inputScore = document.querySelector(`[name="score"]`);
  inputScore.value = submittedQuizData.score;

  Object.keys(submittedQuizData).forEach((key, index) => {
    const hiddenInput = document.querySelector(
      `[name="field-question-${index}"]`
    );
    if (hiddenInput) {
      hiddenInput.value = submittedQuizData[key].selectedAnswer?.answer;
    }
  });
}

/**
 * Removes all control elements from the DOM.
 * @function removeControlElements
 */
function removeControlElements() {
  document
    .querySelectorAll(".qz-control-input")
    .forEach((inputControl) => inputControl.remove());
}

/**
 * Displays success message to the user.
 * @function showSuccess
 * @param {Object} data - Server response data containing the thank you message.
 */
function showSuccess(data) {
  removeControlElements();
  const thankYouMessage = document.createElement("h4");
  thankYouMessage.classList.add("qz-explanation", "qz-question-text");
  thankYouMessage.style.width = "100%";
  thankYouMessage.style.textAlign = "center";
  thankYouMessage.textContent = data.thankyou;
  document.querySelector(".qz-control-group").append(thankYouMessage);
  isFinal = false;
}

/**
 * Displays error message to the user.
 * @function showError
 * @param {Object} error - Error object containing details of the error.
 */
function showError(error) {
  removeControlElements();
  console.error(error);
  const errorMessage = document.createElement("h4");
  errorMessage.classList.add("qz-explanation", "qz-question-text");
  errorMessage.style.color = "#ff000";
  errorMessage.style.textAlign = "center";
  errorMessage.textContent = data.thankyou;
  document.querySelector(".qz-control-group").append(errorMessage);
  isFinal = false;
}

/**
 * Toggle the disabled attribute of the submit button based on email and checkbox validity.
 * @function toggleSubmitButton
 * @returns {void}
 */
function toggleSubmitButton() {
  const isEmailValid = email.checkValidity();
  const isCheckboxChecked = checkbox.checked;
  submitButton.disabled = !(isEmailValid && isCheckboxChecked);
}

email.addEventListener("input", toggleSubmitButton);
checkbox.addEventListener("change", toggleSubmitButton);