<!DOCTYPE html>
<html lang="en">
<head>
    <title>OTP Verification</title>
    <meta name="description" content="OTP verification">
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
            border: 1px solid rgba(252,248,245, 0.14);
            background: var(--paper);
            box-shadow: 0 8px 48px rgba(80, 40, 180, 0.18), inset 0 1px 0 rgba(252,248,245, 0.12);
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
            margin: 0 0 4px;
        }

        .sub-heading {
            max-width: 310px;
            margin: 0;
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

        .otp-inputs {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin-top: 6px;
        }

        .otp-input {
            width: 100%;
            aspect-ratio: 1;
            min-width: 0;
            border: 1px solid var(--env-border);
            border-radius: 12px;
            background: var(--input-field-color);
            color: rgba(15, 1, 1, 0.9);
            font-family: inherit;
            font-size: 24px;
            text-align: center;
            outline: none;
            box-shadow: 5px 5px 10px rgba(2, 2, 2, 0.25);
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        .otp-input:focus {
            border-color: var(--accent);
            background-color: var(--focus-color);
            box-shadow: 0 0 10px rgba(109, 76, 91, 0.45);
        }

        .timer-row {
            min-height: 28px;
            margin-top: 22px;
            text-align: center;
            color: var(--accent);
            font-size: 16px;
            font-weight: 600;
        }

        .message-row {
            min-height: 22px;
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
        }

        .error-message {
            color: #b94b4b;
        }

        .reset-btn {
            width: 100%;
            margin-top: 12px;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background-color: var(--accent);
            color: #fcf8f5;
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

        .reset-btn:disabled {
            cursor: wait;
            opacity: 0.78;
            transform: none;
        }

        .btn-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(252,248,245,0.18) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.5s;
        }

        .reset-btn:hover .btn-shimmer {
            transform: translateX(100%);
        }

        .back-link {
            display: block;
            margin-top: 24px;
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

            .otp-inputs {
                gap: 6px;
            }

            .otp-input {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <section class="reset-shell">
        <div class="reset-card">
            <div class="heading-area">
                <h1 class="main-heading">OTP Verification</h1>
                <p class="sub-heading" id="otpInstruction">Click the button below to send a one-time password to your registered email.</p>
                <div class="decor-line" aria-hidden="true"><span class="decor-dot"></span></div>
            </div>

            <div class="otp-inputs" aria-label="One-time password">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input" value="" autofocus>
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input" value="">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input" value="">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input" value="">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input" value="">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-input" value="">
            </div>

            <div class="timer-row">
                <span class="min"></span><span class="sec"></span>
            </div>

            <button type="button" class="reset-btn" id="resentotp">
                <span class="btn-shimmer"></span>
                <span>Send OTP Code</span>
            </button>

            <div class="message-row">
                <span class="error-message"><span class="atm_time"></span></span>
            </div>

            <a class="back-link" href="<?= URLROOT ?>/users/login">Back to sign in</a>
        </div>
    </section>

    <script>
        const inputs = document.querySelectorAll(".otp-input");
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;

                // Move to next box when typing a digit
                if (value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }

                // Move to previous box when deleting
                if (value.length === 0 && index > 0 && e.inputType === 'deleteContentBackward') {
                    inputs[index - 1].focus();
                }
                const otp = Array.from(inputs).map(input => input.value).join('');
                if(otp.length === inputs.length){
                    sendOtpToServer(otp);
                }
            });
        });

        let attemptCount = 0;
        function sendOtpToServer(otp){
            const atm_time = document.querySelector('.atm_time');

            const data = {otp : otp};

            fetch("<?= URLROOT ?>/otps/otpVerify",{
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            })
            .then(res => res.json())
            .then(data => {
                if(data.otp_try_status){
                    console.log('yes otp success');
                    window.location.href = "<?= URLROOT ?>/" + (data.redirect || "main/home");
                }

                // when attempt times over 3 , lock the account
                if(data.otp_fail){
                    atm_time.innerHTML = "You tried to attempt over 3 times! Try again after 15 minutes";
                }
                console.log(data.otp_fail);
            })
            .catch(err => {
                console.error('Error sending OTP:', err);
            });
        }

        function setTimer() {
            if (window.otpTimer) {
                clearInterval(window.otpTimer);
            }

            let count = 1 * 60;
            const min = document.querySelector('.min');
            const sec = document.querySelector('.sec');

            window.otpTimer = setInterval(() => {
            let minutes = Math.floor(count / 60);
            let seconds = count % 60;

            min.innerHTML = minutes + " : ";
            sec.innerHTML = seconds < 10 ? '0' + seconds : seconds;

            if(seconds == 00){
                min.innerHTML = "Expired OTP";
                sec.innerHTML = " ";
            }

            if (count <= 0) clearInterval(window.otpTimer);
                count--;
            }, 1000);
        }

        const resentotp = document.querySelector("#resentotp");
        const resendBtnText = resentotp.querySelector("span:last-child");
        const otpInstruction = document.querySelector("#otpInstruction");
        let otpHasBeenSent = false;

        resentotp.addEventListener('click',()=>{
            const atm_time = document.querySelector('.atm_time');
            atm_time.innerHTML = "";
            resentotp.disabled = true;
            resendBtnText.textContent = "Sending OTP...";

            fetch("<?= URLROOT ?>/otps/otp", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({send_otp: true}),
            })
            .then(res => res.json())
            .then(data => {
                if(data.status == true){
                    otpHasBeenSent = true;
                    otpInstruction.textContent = "Enter the one-time password sent to your registered email.";
                    atm_time.innerHTML = "OTP code sent. Please check your email.";
                    inputs.forEach(input => input.value = "");
                    inputs[0].focus();
                    setTimer();
                    return;
                }

                atm_time.innerHTML = "Could not send OTP code. Please try again.";
            })
            .catch(err => {
                console.error("Error sending OTP:", err);
                atm_time.innerHTML = "Could not send OTP code. Please try again.";
            })
            .finally(() => {
                resentotp.disabled = false;
                resendBtnText.textContent = otpHasBeenSent ? "Resend OTP Code" : "Send OTP Code";
            });
        })
    </script>
</body>
</html>
