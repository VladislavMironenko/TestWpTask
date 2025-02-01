<div id="wp-chat-plugin">
    <div id="contact-form">
        <h3>Connect with Us</h3>
        <input type="text" id="user-name" placeholder="Your Name" required />
        <input type="email" id="user-email" placeholder="Your Email" required />
        <input type="text" id="user-phone" placeholder="Your Phone Number" required />
        <button id="start-chat">Start Chat</button>
    </div>

    <div id="survey-form" style="display:none;">
        <h3>Survey</h3>
        <div id="chat-box-question"></div>
        <button id="next-question">Next</button>
    </div>

    <div id="chat-window" style="display:none;">
        <div id="chat-box"></div>
        <input type="text" id="user-message" placeholder="Type message..." />
        <button id="send-message">Send</button>
    </div>
</div>
<script>
  document.getElementById('start-chat').onclick = function() {
    var userMessageName = document.getElementById('user-name').value;
    var userMessageEmail = document.getElementById('user-email').value;
    var userMessagePhone = document.getElementById('user-phone').value;
 
    // Проверка на заполненность полей
    if (userMessageName && userMessageEmail && userMessagePhone) {
        // Отправка данных как URL-encoded
        var formData = new FormData();
        formData.append('action', 'save_chat_message');
        formData.append('name', userMessageName);
        formData.append('email', userMessageEmail);
        formData.append('phone', userMessagePhone);
        formData.append('nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        document.getElementById('contact-form').style.display = 'none';
        document.getElementById('survey-form').style.display = 'block';
        loadSurveyQuestions();
        // document.getElementById('chat-box').innerHTML += '<div>' + userMessageName + ' (' + userMessageEmail + ', ' + userMessagePhone + ')</div>';
        // .catch(error => {
        //     console.error('Error:', error);
        // });
    } else {
        alert("Please fill in all fields.");
    }
};
var currentQuestionIndex = 0;
var surveyAnswers = []; 
var surveyQuestion = [];
var result_questions = [];
var surveyQuestions = [
        "How satisfied are you with our service?",
        "Would you recommend our service to others?",
        "Any additional comments?"
    ];

function loadSurveyQuestions() {
    // Очистка чата перед добавлением нового вопроса
    var chatBox = document.getElementById('chat-box-question');
    chatBox.innerHTML = '';  // Очищаем чат

    // Добавляем текущий вопрос в chat-box-questionx
    if (currentQuestionIndex < surveyQuestions.length) {
        var question = surveyQuestions[currentQuestionIndex];
        surveyQuestion.push(question)

        // Создаем элемент вопроса и поля для ввода ответа
        var questionElement = document.createElement('div');
        questionElement.classList.add('survey-question');
        questionElement.innerHTML = `
            <label>${question}</label><br>
            <input type="text" id="survey-answer-${currentQuestionIndex}" placeholder="Your answer" required /><br>
        `;
        chatBox.appendChild(questionElement);
    }
}

// Переключение на следующий вопрос при нажатии кнопки "Next"
document.getElementById('next-question').onclick = function() {
    var currentInput = document.getElementById(`survey-answer-${currentQuestionIndex}`);

    // Проверяем, что пользователь ответил на вопрос
    if (!currentInput.value) {
        alert("Please answer the question.");
        return;
    }

    // Сохраняем ответ на текущий вопрос
    surveyAnswers.push(currentInput.value);

    // Переходим к следующему вопросу
    currentQuestionIndex++;

    // Очищаем поле ввода для следующего вопроса
    currentInput.value = '';

    // Блокируем кнопку "Next" во время загрузки данных
    document.getElementById('next-question').disabled = true;

    // Если вопросы не закончились, показываем следующий вопрос
    if (currentQuestionIndex < surveyQuestions.length) {
        loadSurveyQuestions(); // Загружаем следующий вопрос
        // Разблокируем кнопку после обновления вопросов
        document.getElementById('next-question').disabled = false;
    } else {
        // Если все вопросы пройдены, отправляем данные на сервер
        var formData = new FormData();
        formData.append('action', 'save_survey_answers');
        for (let i = 0; i < surveyQuestion.length; i++) {
            result_questions.push(surveyQuestion[i] + " - " + surveyAnswers[i]);
        }
        formData.append('answers', JSON.stringify(result_questions));
        formData.append('nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        // Закрываем опрос и показываем окно чата
        document.getElementById('survey-form').style.display = 'none';
        document.getElementById('chat-window').style.display = 'block';
    }
};

document.getElementById('send-message').onclick = function() {
        var userMessage = document.getElementById('user-message').value;
        if (userMessage){
        var formData = new FormData();
        formData.append('action', 'save_chat_message');
        formData.append('message', userMessage);
        formData.append('nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Добавляем ответ от AI
                    document.getElementById('chat-box').innerHTML += '<div>Bot: ' + data.data.ai_response + '</div>';
                }
            })
        // Добавляем ответ пользователя в чат
        document.getElementById('chat-box').innerHTML += '<div>You: ' + userMessage + '</div>';
        // Очищаем поле ввода
        document.getElementById('user-message').value = '';
        } else {
            alert("Please fill in all fields.");
        }
    }
</script>
<style>
#survey-form {
    padding: 20px;
    background-color: #ffffff;
    display: none;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

#survey-question-container {
    margin-bottom: 15px;
}

.survey-question {
    margin-bottom: 10px;
}

.survey-question input {
    width: 92%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background-color: #f9f9f9;
}

#wp-chat-plugin {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    width: 350px;
    background: linear-gradient(135deg, #333, #444);
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    margin: 50px auto;
    padding: 20px;
}


#contact-form {
    padding: 20px;
    background-color: #ffffff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

#contact-form h3 {
    margin-bottom: 15px;
    font-size: 20px;
    text-align: center;
    color: #333;
}

#contact-form input {
    width: 92%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    background-color: #f9f9f9;
    color: #333;
}

#contact-form input:focus {
    border-color: black;
    outline: none;
}

#contact-form button {
    width: 100%;
    padding: 15px;
    background-color: #222;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

#contact-form button:hover {
    background-color: #444;
}


#chat-window {
    padding: 20px;
    background-color: #ffffff;
    display: none;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

#chat-box {
    max-height: 350px;
    overflow-y: auto;
    margin-bottom: 15px;
    padding-right: 10px;
    border-bottom: 1px solid #ddd;
    color: #333;
}

#chat-box div {
    margin-bottom: 15px;
    font-size: 14px;
    line-height: 1.6;
}

#chat-box div.user {
    text-align: right;
    color: #222;
    background-color: #eaeaea;
    border-radius: 12px;
    padding: 8px 15px;
    max-width: 70%;
    margin-left: auto;
}

#chat-box div.ai {
    text-align: left;
    color: #fff;
    background-color: #0078d4;
    border-radius: 12px;
    padding: 8px 15px;
    max-width: 70%;
    margin-right: auto;
}

#user-message {
    width: calc(100% - 24px);
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: black;
}
</style>