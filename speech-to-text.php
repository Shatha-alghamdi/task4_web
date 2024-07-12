<?php
// ربط قاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_transcript_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// تحقق من طلب إرسال البيانات
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transcript = $_POST["transcript"];

    $sql = "INSERT INTO transcripts (text) VALUES ('$transcript')";
    if ($conn->query($sql) === TRUE) {
        $last_id = $conn->insert_id;
        echo "Transcript saved to the database. The ID of the inserted transcript is: " . $last_id;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Speech-to-Text Recorder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        h1 {
            margin-top: 50px;
            font-size: 36px;
            color: #333;
        }
        
        #recordButton {
            margin-top: 30px;
            font-size: 18px;
            padding: 10px 20px;
            border-radius: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        #recordButton.stop {
            background-color: #ff0000;
        }
        
        #transcript {
            margin-top: 30px;
            font-size: 24px;
            font-weight: bold;
            padding: 20px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <h1>Speak, please</h1>
    <button id="recordButton">Start Recording</button>
    <div id="transcript"></div>

    <script>
        const recordButton = document.getElementById("recordButton");
        const transcriptDisplay = document.getElementById("transcript");

        let recognition;

        if (window.SpeechRecognition || window.webkitSpeechRecognition) {
            recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.continuous = true;
            recognition.interimResults = true;

            recordButton.addEventListener("click", () => {
                if (recordButton.textContent === "Start Recording") {
                    recognition.start();
                    recordButton.textContent = "Stop Recording";
                    recordButton.classList.add("stop");
                } else {
                    recognition.stop();
                    recordButton.textContent = "Start Recording";
                    recordButton.classList.remove("stop");

                    const transcript = transcriptDisplay.textContent;
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            console.log(xhr.responseText);
                        }
                    };
                    xhr.send("transcript=" + encodeURIComponent(transcript));
                }
            });

            recognition.addEventListener("result", (event) => {
                let transcript = "";
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        transcript += event.results[i][0].transcript;
                    }
                }
                transcriptDisplay.textContent = transcript;
            });
        } else {
            transcriptDisplay.textContent = "Sorry, your browser does not support speech recognition.";
        }
    </script>
</body>
</html>