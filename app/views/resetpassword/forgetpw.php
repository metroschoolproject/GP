
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forget Password</title>
    <meta name="description" content="Login">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
    <?php $dashboardCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>

    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/app.css">
    <style>
        :root {
            --env-bg: #e8b4b8;
            --env-border: #f4c7c4e5;
            --paper: #f5e8d9;
            --accent: #6d4c5b;
            --header-font: "Pinyon Script", cursive;
            --body-font: serif;
            --focus-color: rgb(247, 236, 236);
            --input-field-color: rgba(249, 237, 228, 0.9);
            --placeholder: rgba(141, 140, 140, 0.743);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--body-font);
            background: var(--env-bg);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        .reset-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 18px;
        }

        .reset-card {
            width: 100%;
            max-width: 420px;
            min-height: 460px;
            padding: 38px 40px 40px;
            position: relative;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: var(--paper);
            box-shadow: 0 8px 48px rgba(80, 40, 180, 0.18), inset 0 1px 0 rgba(255, 255, 255, 0.12);
            overflow: hidden;
        }

        .heading-area {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 28px;
        }

        .main-heading {
            font-family: var(--header-font);
            font-size: 46px;
            font-weight: 600;
            color: var(--accent);
            line-height: 1.1;
            margin-bottom: 4px;
        }

        .sub-heading {
            max-width: 310px;
            font-size: 15px;
            line-height: 1.45;
            color: var(--accent);
        }

        .decor-line {
            width: 188px;
            height: 12px;
            margin-top: 8px;
            opacity: 0.95;
            position: relative;
        }

        .decor-line::before,
        .decor-line::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 78px;
            height: 1px;
            background: var(--accent);
            opacity: 0.75;
        }

        .decor-line::before {
            left: 0;
        }

        .decor-line::after {
            right: 0;
        }

        .decor-dot {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 8px;
            height: 8px;
            border: 1px solid var(--accent);
            transform: translate(-50%, -50%) rotate(45deg);
            opacity: 0.85;
        }

        .decorated-input {
            position: relative;
            width: 100%;
            margin-bottom: 12px;
        }

        .decorated-input input {
            width: 100%;
            height: 58px;
            background: var(--input-field-color);
            border: 1px solid var(--env-border);
            border-radius: 12px;
            padding: 20px 16px 12px;
            font-size: 18px;
            color: rgba(15, 1, 1, 0.9);
            outline: none;
            font-family: inherit;
            box-shadow: 5px 5px 10px rgba(2, 2, 2, 0.25);
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        .decorated-input input::placeholder {
            color: transparent;
        }

        .decorated-input label {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            color: var(--placeholder);
            pointer-events: none;
            transition: all 0.25s ease;
        }

        .decorated-input:hover label,
        .decorated-input input:focus + label,
        .decorated-input input:not(:placeholder-shown) + label {
            top: 5px;
            transform: translateY(0);
            color: var(--accent);
            letter-spacing: 0.3px;
        }

        .decorated-input input:focus {
            border-color: var(--accent);
            background-color: var(--focus-color);
            box-shadow: 0 0 10px rgba(109, 76, 91, 0.45);
        }

        .reset-btn {
            width: 100%;
            margin-top: 12px;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background-color: var(--accent);
            color: white;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.2px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 5px 5px 8px rgba(0, 0, 0, 0.45);
            transition: transform 0.15s, box-shadow 0.15s, opacity 0.2s;
        }

        .reset-btn:hover {
            transform: translateY(-1px);
        }

        .btn-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.18) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.5s;
        }

        .reset-btn:hover .btn-shimmer {
            transform: translateX(100%);
        }

        .message-row {
            min-height: 22px;
            margin-top: 14px;
            text-align: center;
            font-size: 13px;
        }

        .error-message {
            color: #b94b4b;
        }

        .success-message {
            color: #15803d;
        }

        .back-link {
            display: block;
            margin-top: 26px;
            text-align: center;
            color: var(--accent);
            font-size: 14px;
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        @media (max-width: 480px) {
            .reset-card {
                padding: 34px 24px 32px;
            }

            .main-heading {
                font-size: 40px;
            }
        }
    </style>
</head>
<body>
    <section class="reset-shell">
        <div class="reset-card">
            <div class="heading-area">
                <h1 class="main-heading">Forgot Password</h1>
                <p class="sub-heading">Enter your registered email and we will send a reset link to your inbox.</p>
                <div class="decor-line" aria-hidden="true"><span class="decor-dot"></span></div>
            </div>

            <div class="decorated-input">
                <input id="resetEmail" type="email" value="" autofocus placeholder=" " autocomplete="email">
                <label for="resetEmail">Email</label>
            </div>

            <button type="button" class="reset-btn" id="resetSubmitBtn">
                <span class="btn-shimmer"></span>
                <span>Send Reset Link</span>
            </button>

            <div class="message-row">
                <span class="error-message hidden emailmessage">Email isn't registered.</span>
                <span class="error-message hidden mailmessage">Could not send reset email. Please try again later.</span>
                <span class="success-message hidden sentmessage">We sent a reset link to this email.</span>
            </div>

            <a class="back-link" href="<?= URLROOT ?>/users/login">Back to sign in</a>
        </div>
    </section>
    <script>
        const submitbtn = document.querySelector("#resetSubmitBtn");
        const emailmessage = document.querySelector('.emailmessage');
        const mailmessage = document.querySelector('.mailmessage');
        const sentmessage = document.querySelector('.sentmessage');
        submitbtn.addEventListener("click",()=>{
            const input = document.querySelector("#resetEmail");
            const value = input.value;
                       const data = {email : value};

            fetch("<?= URLROOT ?>/resetpassword/singleresettoken",{
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            })
            .then(res => res.json())
            .then(data => {
                if(data.e_registered == false){
                    emailmessage.classList.remove('hidden');
                    sentmessage.classList.add('hidden');
                    mailmessage.classList.add('hidden');
                }else if(data.status === 'error'){
                    mailmessage.classList.remove('hidden');
                    sentmessage.classList.add('hidden');
                    emailmessage.classList.add('hidden');
                }else{
                    sentmessage.classList.remove('hidden');
                    emailmessage.classList.add('hidden');
                    mailmessage.classList.add('hidden');

                }
                console.log(data);
            })
            .catch(err => {
                console.error('Error sending OTP:', err);
            });

        })


        

    </script>
</body>
</html>
