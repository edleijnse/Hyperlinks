<head>
    <title>ChatGPT client</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f7f7;
            color: #444444;
            margin: 0;
            padding: 0;
        }
        .input {
            font-size: 34px;
            color: #4054b2;
            background-color: ghostwhite;
            text-align: start;
            padding: 10px;
            border-radius: 5px;
        }
        .list {
            font-size: 34px;
            color: #d4af37;
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .output {
            background-color: #f7f7f7;
            color: #707070;
            font-size: 34px;
            padding: 10px;
            border-radius: 5px;
        }
        .ask {
            height: 100px;
            width: 250px;
            font-size: 34px;
            color: #3b5998;
        }
        .heading {
            font-size: 34px;
            font-weight: 500;
            color: #3b5998;
        }
        .error-message {
            color: #ff6161;
            background-color: #ffe6e6;
            padding: 10px;
            border-radius: 5px;
            font-size: 24px;
            margin: 20px 0;
        }
        .large-font {
            font-size: 72px;
            color: #3b5998;
        }
        .medium-font {
            font-size: 24px; /* Adjust this value as needed for your desired font size */
            color: Darkblue
        }
        .red-background {
            background-color: blanchedalmond;
        }
        .green-background {
            background-color: yellowgreen;
        }
        :root {
            --mobile-font-size: 72px;
            --mobile-bg-color: lightblue;
            --mobile-width: 100%;
            --mobile-padding: 8px;
            --mobile-margin-top: 20px;
        }

        @media screen and (max-width: 1000px) {
            body {
                font-size: var(--mobile-font-size);
                background-color: var(--mobile-bg-color);
            }

            table {
                width: var(--mobile-width);
            }

            td, th {
                padding: var(--mobile-padding);
            }

            form {
                margin-top: var(--mobile-margin-top);
            }
        }
        @keyframes blink {
            0% {opacity: 1;}
            50% {opacity: 0.4;}
            100% {opacity: 1;}
        }
        .blink-text {
            animation: blink 1s infinite;
        }
    </style>
</head>