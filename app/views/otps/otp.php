<!DOCTYPE html>
<html lang="en">
<head>
    <title>OTP Verification - <?= APPNAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5e8d9;
            --card: #faf5ef;
            --accent: #6d4c5b;
            --muted: #b79c8b;
            --border: #ead8c7;
            --white: #fcf8f5;
            --gold: #d4a047;
            --gold-light: rgba(212,160,71,0.12);
            --input-bg: #fcf8f5;
            --header-font: 'Playfair Display', Georgia, serif;
            --body-font: 'Poppins', system-ui, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: var(--body-font);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 460px;
            background: var(--card);
            border-radius: 24px;
            border: 1.5px solid #dbc6b0;
            box-shadow: 0 20px 40px rgba(15,23,42,0.08), 0 0 0 1px rgba(212,160,71,0.06), 0 0 40px rgba(212,160,71,0.04);
            padding: 36px 36px 32px;
            text-align: center;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.7s cubic-bezier(0.4,0,0.2,1) 0.1s forwards;
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Loading overlay */
        .loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 10;
            display: none;
            align-items: center;
            justify-content: center;
            border-radius: 24px;
            background: rgba(245,232,217,0.88);
            backdrop-filter: blur(8px);
        }
        .loading-overlay.show { display: flex; }
        .loading-panel { display: flex; flex-direction: column; align-items: center; gap: 14px; }
        .loading-spinner { width: 36px; height: 36px; animation: spin 1s cubic-bezier(0.4,0,0.2,1) infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 13px; font-weight: 600; color: var(--accent); }

        /* Heading */
        .heading-area { margin-bottom: 24px; }
        .main-heading {
            font-family: var(--header-font);
            font-size: 32px;
            font-weight: 600;
            color: var(--accent);
            line-height: 1.2;
            margin-bottom: 6px;
        }
        .sub-heading {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.5;
            max-width: 320px;
            margin: 0 auto;
        }
        .decor-line {
            width: 160px;
            height: 12px;
            margin: 10px auto 0;
            position: relative;
        }
        .decor-line::before, .decor-line::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 65px;
            height: 1px;
            background: var(--border);
        }
        .decor-line::before { left: 0; }
        .decor-line::after { right: 0; }
        .decor-dot {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 7px;
            height: 7px;
            border: 1px solid var(--border);
            background: var(--card);
            transform: translate(-50%, -50%) rotate(45deg);
        }

        /* OTP inputs */
        .otp-inputs {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 8px;
            margin: 20px 0;
        }
        .otp-input {
            width: 100%;
            aspect-ratio: 1;
            min-width: 0;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            background: var(--input-bg);
            color: var(--accent);
            font-family: var(--body-font);
            font-size: 22px;
            font-weight: 600;
            text-align: center;
            outline: none;
            box-shadow: 0 1px 3px rgba(44,36,32,0.04);
            transition: border-color 0.35s cubic-bezier(0.4,0,0.2,1),
                        background 0.35s cubic-bezier(0.4,0,0.2,1),
                        box-shadow 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .otp-input:focus {
            border-color: var(--gold);
            background: var(--white);
            box-shadow: 0 0 0 3px var(--gold-light), 0 4px 12px rgba(212,160,71,0.1);
        }

        /* Timer */
        .timer-row {
            min-height: 28px;
            margin-bottom: 16px;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            color: var(--accent);
            letter-spacing: 0.5px;
        }

        /* Button */
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent) 0%, #8b5e6f 100%);
            color: var(--white);
            font-family: var(--body-font);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(109,76,91,0.22);
            transition: transform 0.4s cubic-bezier(0.4,0,0.2,1),
                        box-shadow 0.4s cubic-bezier(0.4,0,0.2,1),
                        opacity 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(109,76,91,0.28);
        }
        .btn:disabled { cursor: wait; opacity: 0.7; transform: none; }
        .btn-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(252,248,245,0.18) 50%, transparent 100%);
            transform: translateX(-100%);
        }
        .btn:hover .btn-shimmer { animation: shimmer 0.8s ease; }
        @keyframes shimmer { to { transform: translateX(100%); } }

        /* Message */
        .message-row {
            min-height: 20px;
            margin-top: 16px;
            text-align: center;
            font-size: 13px;
        }
        .error-message { color: #b94b4b; }
        .success-message { color: #16a34a; }

        /* Back link */
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            text-decoration: none;
            transition: color 0.3s;
        }
        .back-link:hover { color: var(--accent); }
        .back-link span { color: var(--accent); font-weight: 500; text-decoration: underline; text-underline-offset: 2px; }

        @media (max-width: 480px) {
            .card { padding: 30px 24px 28px; }
            .main-heading { font-size: 28px; }
            .otp-inputs { gap: 6px; }
            .otp-input { font-size: 18px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="loading-overlay" id="otpLoadingOverlay">
            <div class="loading-panel" role="status">
                <svg class="loading-spinner" viewBox="0 0 40 40" fill="none">
                    <circle cx="20" cy="20" r="16" stroke="rgba(109,76,91,0.12)" stroke-width="3.5" fill="none"/>
                    <path d="M20 4a16 16 0 0 1 16 16" stroke="#6d4c5b" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                </svg>
                <span class="loading-text" id="otpLoadingText">Verifying OTP...</span>
            </div>
        </div>

        <div class="heading-area">
            <h1 class="main-heading">OTP Verification</h1>
            <p class="sub-heading" id="otpInstruction">Click the button below to send a one-time password to your registered email.</p>
            <div class="decor-line" aria-hidden="true"><span class="decor-dot"></span></div>
        </div>

        <div class="otp-inputs" aria-label="One-time password">
            <input type="text" inputmode="numeric" maxlength="1" class="otp-input" autofocus>
            <input type="text" inputmode="numeric" maxlength="1" class="otp-input">
            <input type="text" inputmode="numeric" maxlength="1" class="otp-input">
            <input type="text" inputmode="numeric" maxlength="1" class="otp-input">
            <input type="text" inputmode="numeric" maxlength="1" class="otp-input">
            <input type="text" inputmode="numeric" maxlength="1" class="otp-input">
        </div>

        <div class="timer-row">
            <span class="min"></span><span class="sec"></span>
        </div>

        <button type="button" class="btn" id="resentotp">
            <span class="btn-shimmer"></span>
            <span>Send OTP Code</span>
        </button>

        <div class="message-row">
            <span class="error-message"><span class="atm_time"></span></span>
        </div>

        <a class="back-link" href="<?= URLROOT ?>/users/auth">← Back to <span>sign in</span></a>
    </div>

    <script>
        const inputs = document.querySelectorAll(".otp-input");
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                const value = e.target.value;
                if (value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                if (value.length === 0 && index > 0 && e.inputType === 'deleteContentBackward') {
                    inputs[index - 1].focus();
                }
                const otp = Array.from(inputs).map(i => i.value).join('');
                if (otp.length === inputs.length) {
                    sendOtpToServer(otp);
                }
            });
        });

        let attemptCount = 0;
        function sendOtpToServer(otp) {
            const atm_time = document.querySelector('.atm_time');
            const overlay = document.getElementById('otpLoadingOverlay');

            if (overlay) overlay.classList.add('show');
            inputs.forEach(inp => inp.disabled = true);

            fetch("<?= URLROOT ?>/otps/otpVerify", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ otp: otp }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.otp_try_status) {
                    document.getElementById('otpLoadingText').textContent = 'Redirecting...';
                    window.location.href = "<?= URLROOT ?>/" + (data.redirect || "main/home");
                    return;
                }
                if (overlay) overlay.classList.remove('show');
                inputs.forEach(inp => inp.disabled = false);

                if (data.otp_fail) {
                    atm_time.innerHTML = "Too many attempts. Try again after 15 minutes.";
                }
            })
            .catch(err => {
                if (overlay) overlay.classList.remove('show');
                inputs.forEach(inp => inp.disabled = false);
                console.error('Error sending OTP:', err);
            });
        }

        function setTimer() {
            if (window.otpTimer) clearInterval(window.otpTimer);
            let count = 1 * 60;
            const min = document.querySelector('.min');
            const sec = document.querySelector('.sec');

            window.otpTimer = setInterval(() => {
                let minutes = Math.floor(count / 60);
                let seconds = count % 60;
                min.innerHTML = minutes + " : ";
                sec.innerHTML = seconds < 10 ? '0' + seconds : seconds;
                if (seconds == 0) { min.innerHTML = "Expired OTP"; sec.innerHTML = " "; }
                if (count <= 0) clearInterval(window.otpTimer);
                count--;
            }, 1000);
        }

        const resentotp = document.querySelector("#resentotp");
        const resendBtnText = resentotp.querySelector("span:last-child");
        const otpInstruction = document.querySelector("#otpInstruction");
        let otpHasBeenSent = false;

        resentotp.addEventListener('click', () => {
            const atm_time = document.querySelector('.atm_time');
            atm_time.innerHTML = "";
            atm_time.className = 'error-message';
            resentotp.disabled = true;
            resendBtnText.textContent = "Sending OTP...";

            fetch("<?= URLROOT ?>/otps/otp", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ send_otp: true }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.status == true) {
                    otpHasBeenSent = true;
                    otpInstruction.textContent = "Enter the one-time password sent to your registered email.";
                    atm_time.className = 'success-message';
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
        });
    </script>
</body>
</html>
