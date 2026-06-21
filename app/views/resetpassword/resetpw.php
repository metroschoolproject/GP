<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <meta name="description" content="Reset password">
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

        .hidden {
            display: none;
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
            min-height: 520px;
            padding: 38px 40px 40px;
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

        .decorated-input {
            position: relative;
            width: 100%;
            margin-bottom: 14px;
        }

        .decorated-input input {
            width: 100%;
            height: 58px;
            background: var(--input-field-color);
            border: 1px solid var(--env-border);
            border-radius: 12px;
            padding: 20px 48px 12px 16px;
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

        .eye-btn {
            position: absolute;
            right: 13px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            padding: 0;
            border: none;
            background: transparent;
            color: var(--accent);
            cursor: pointer;
            opacity: 0.62;
            transition: opacity 0.2s;
        }

        .eye-btn:hover {
            opacity: 1;
        }

        .password-rules {
            margin: 0 0 10px;
            padding: 0 4px;
            display: grid;
            gap: 6px;
            color: #b94b4b;
            font-size: 13px;
            line-height: 1.3;
        }

        .password-rules span {
            transition: color 0.2s;
        }

        .password-rules span.valid {
            color: #15803d;
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
            transition: transform 0.15s, opacity 0.2s;
        }

        .reset-btn:hover {
            transform: translateY(-1px);
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
        }
    </style>
</head>
<body>
    <section class="reset-shell">
        <div class="reset-card">
            <div class="heading-area">
                <h1 class="main-heading">Reset Password</h1>
                <p class="sub-heading">Create a new password with at least eight characters and a mix of symbols.</p>
                <div class="decor-line" aria-hidden="true"><span class="decor-dot"></span></div>
            </div>

            <div class="decorated-input">
                <input type="password" placeholder=" " name="password" id="password" value="" autocomplete="new-password" autofocus>
                <label for="password">New Password</label>
                <button type="button" class="eye-btn" id="togglePassword" aria-label="Toggle password visibility">
                    <svg class="psw_open_eye hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>
                        <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>
                    </svg>
                    <svg class="psw_close_eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"></path>
                    </svg>
                </button>
            </div>

            <div class="password-rules psw_invalid_ctn hidden">
                <span id="lowerchar">Lowercase character</span>
                <span id="upperchar">Uppercase character</span>
                <span id="numberchar">Number</span>
                <span id="specialchar">Special character</span>
                <span id="pwlength">At least 8 characters</span>
            </div>

            <button type="button" id="submitbtn" class="reset-btn">
                <span class="btn-shimmer"></span>
                <span>Update Password</span>
            </button>

            <div class="message-row">
                <span class="error-message hidden" id="passwordMessage">Please complete every password rule.</span>
                <span class="error-message hidden" id="tokenMessage">This reset link is invalid or expired.</span>
                <span class="success-message hidden" id="successMessage">Password updated successfully.</span>
            </div>

            <a class="back-link" href="<?= URLROOT ?>/users/login">Back to sign in</a>
        </div>
    </section>

    <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($token) ?>">
    <input type="hidden" id="email" name="email" value="<?= htmlspecialchars($email) ?>">

    <script>
        const password = document.getElementById("password");
        const submitbtn = document.getElementById("submitbtn");
        const token = document.getElementById("token");
        const email = document.getElementById("email");
        const pswInvalidCtn = document.querySelector(".psw_invalid_ctn");
        const passwordMessage = document.getElementById("passwordMessage");
        const tokenMessage = document.getElementById("tokenMessage");
        const successMessage = document.getElementById("successMessage");
        const togglePassword = document.getElementById("togglePassword");
        const openEye = document.querySelector(".psw_open_eye");
        const closeEye = document.querySelector(".psw_close_eye");

        const rules = [
            { test: /[a-z]/, element: document.getElementById("lowerchar") },
            { test: /[A-Z]/, element: document.getElementById("upperchar") },
            { test: /[0-9]/, element: document.getElementById("numberchar") },
            { test: /[~!@#$%^&*()]/, element: document.getElementById("specialchar") },
            { test: /.{8,}/, element: document.getElementById("pwlength") }
        ];

        let isStrongPw = false;

        password.addEventListener("input", () => {
            pswInvalidCtn.classList.remove("hidden");
            passwordMessage.classList.add("hidden");
            tokenMessage.classList.add("hidden");
            successMessage.classList.add("hidden");
            validatePassword(password.value);
        });

        togglePassword.addEventListener("click", () => {
            const showPassword = password.type === "password";
            password.type = showPassword ? "text" : "password";
            openEye.classList.toggle("hidden", !showPassword);
            closeEye.classList.toggle("hidden", showPassword);
        });

        submitbtn.addEventListener("click", () => {
            validatePassword(password.value);
            tokenMessage.classList.add("hidden");
            successMessage.classList.add("hidden");

            if (!isStrongPw) {
                pswInvalidCtn.classList.remove("hidden");
                passwordMessage.classList.remove("hidden");
                password.focus();
                return;
            }

            pswInvalidCtn.classList.add("hidden");
            passwordMessage.classList.add("hidden");

            const data = {
                password: safeInput("password"),
                token: token.value,
                email: email.value
            };

            fetch("<?= URLROOT ?>/resetpassword/setnewpassword", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === "inspired" || res.status === "error") {
                    tokenMessage.classList.remove("hidden");
                    return;
                }

                if (res.pw_status === true) {
                    successMessage.classList.remove("hidden");
                    submitbtn.style.opacity = "0.78";
                    return;
                }

                passwordMessage.textContent = "Could not update password. Please try again.";
                passwordMessage.classList.remove("hidden");
            })
            .catch(err => {
                console.error("Fetch error:", err);
                passwordMessage.textContent = "Could not update password. Please try again.";
                passwordMessage.classList.remove("hidden");
            });
        });

        function validatePassword(pw) {
            const invalidChars = /[`\-+<>?|]/;

            if (invalidChars.test(pw)) {
                passwordMessage.textContent = "Password contains invalid characters.";
                passwordMessage.classList.remove("hidden");
                isStrongPw = false;
                return;
            }

            isStrongPw = rules.every(rule => {
                const pass = rule.test.test(pw);
                rule.element.classList.toggle("valid", pass);
                return pass;
            });
        }

        function encodeHTML(str) {
            return str.replace(/[&<>"']/g, function (char) {
                const entities = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                };
                return entities[char];
            });
        }

        function safeInput(name) {
            const el = document.querySelector(`input[name='${name}']`);
            return encodeHTML(el.value.trim());
        }
    </script>
</body>
</html>
