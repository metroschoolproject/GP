<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password - <?= APPNAME ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.7s cubic-bezier(0.4,0,0.2,1) 0.1s forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }
        .hidden { display: none; }

        .heading-area { text-align: center; margin-bottom: 24px; }
        .icon-circle {
            width: 56px; height: 56px; border-radius: 50%;
            background: linear-gradient(135deg, rgba(109,76,91,0.08), rgba(212,160,71,0.08));
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
        }
        .icon-circle svg { color: var(--accent); }
        h1 {
            font-family: var(--header-font); font-size: 28px; font-weight: 600;
            color: var(--accent); margin-bottom: 6px; line-height: 1.2;
        }
        .subtitle { font-size: 14px; color: var(--muted); line-height: 1.5; }
        .decor-line { width: 160px; height: 12px; margin: 12px auto 0; position: relative; }
        .decor-line::before, .decor-line::after {
            content: ''; position: absolute; top: 50%; width: 65px; height: 1px; background: var(--border);
        }
        .decor-line::before { left: 0; }
        .decor-line::after { right: 0; }
        .decor-dot {
            position: absolute; left: 50%; top: 50%; width: 7px; height: 7px;
            border: 1px solid var(--border); background: var(--card);
            transform: translate(-50%, -50%) rotate(45deg);
        }

        /* Input */
        .input-group { margin-bottom: 6px; }
        .decorated-input { position: relative; width: 100%; }
        .decorated-input input {
            width: 100%; height: 54px;
            background: var(--input-bg);
            border: 1.5px solid var(--border); border-radius: 12px;
            padding: 20px 48px 12px 16px; font-size: 15px;
            color: var(--accent); outline: none;
            font-family: var(--body-font); font-weight: 500;
            box-shadow: 0 1px 3px rgba(44,36,32,0.04);
            transition: border-color 0.35s cubic-bezier(0.4,0,0.2,1),
                        background 0.35s cubic-bezier(0.4,0,0.2,1),
                        box-shadow 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .decorated-input input::-webkit-credentials-auto-fill-button,
        .decorated-input input::-ms-reveal { display: none !important; }
        .decorated-input input::placeholder { color: transparent; }
        .decorated-input label {
            position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
            font-size: 13px; font-weight: 500; color: var(--muted);
            pointer-events: none;
            transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
        }
        .decorated-input:hover label,
        .decorated-input input:focus + label,
        .decorated-input input:not(:placeholder-shown) + label {
            top: 4px; transform: translateY(0);
            font-size: 11px; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.04em; color: var(--accent);
        }
        .decorated-input input:focus {
            border-color: var(--gold); background: var(--white);
            box-shadow: 0 0 0 3px var(--gold-light), 0 4px 12px rgba(212,160,71,0.08);
        }

        /* Eye button */
        .eye-btn {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            display: flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; padding: 0;
            border: none; background: transparent;
            color: var(--accent); cursor: pointer; opacity: 0.5;
            transition: opacity 0.3s cubic-bezier(0.4,0,0.2,1);
        }
        .eye-btn:hover { opacity: 1; }

        /* Password rules */
        .password-rules {
            margin: 0 0 16px; padding: 10px 12px;
            display: grid; grid-template-columns: 1fr 1fr; gap: 4px 12px;
            background: rgba(212,160,71,0.03);
            border: 1px solid rgba(212,160,71,0.06);
            border-radius: 12px;
            font-size: 11px; font-weight: 500; line-height: 1.5;
        }
        .password-rules span {
            display: flex; align-items: center; gap: 7px;
            padding: 2px 0; color: var(--muted);
            transition: color 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .password-rules span::before {
            content: ''; display: inline-block; width: 13px; height: 13px;
            border-radius: 4px; border: 1.5px solid rgba(109,76,91,0.18); flex-shrink: 0;
            transition: background 0.4s cubic-bezier(0.4,0,0.2,1),
                        border-color 0.4s cubic-bezier(0.4,0,0.2,1),
                        box-shadow 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .password-rules span.valid { color: #5b8c5a; }
        .password-rules span.valid::before {
            background: #5b8c5a; border-color: #5b8c5a;
            box-shadow: 0 0 6px rgba(91,140,90,0.2);
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 12 12' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2.5 6L5 8.5L9.5 3.5' stroke='white' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-size: 9px; background-position: center; background-repeat: no-repeat;
        }

        /* Button */
        .btn {
            width: 100%; padding: 14px; border: none; border-radius: 12px;
            background: linear-gradient(135deg, var(--accent) 0%, #8b5e6f 100%);
            color: var(--white); font-family: var(--body-font);
            font-size: 15px; font-weight: 600; letter-spacing: 0.3px;
            cursor: pointer; position: relative; overflow: hidden;
            box-shadow: 0 4px 14px rgba(109,76,91,0.22);
            transition: transform 0.4s cubic-bezier(0.4,0,0.2,1),
                        box-shadow 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(109,76,91,0.28); }
        .btn-shimmer {
            position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent 0%, rgba(252,248,245,0.18) 50%, transparent 100%);
            transform: translateX(-100%);
        }
        .btn:hover .btn-shimmer { animation: shimmer 0.8s ease; }
        @keyframes shimmer { to { transform: translateX(100%); } }

        /* Messages */
        .message-row { min-height: 20px; margin-top: 16px; text-align: center; font-size: 13px; }
        .error-message { color: #b94b4b; }
        .success-message { color: #16a34a; }

        .back-link {
            display: block; margin-top: 20px; text-align: center;
            font-size: 13px; color: var(--muted); text-decoration: none;
            transition: color 0.3s;
        }
        .back-link:hover { color: var(--accent); }
        .back-link span { color: var(--accent); font-weight: 500; text-decoration: underline; text-underline-offset: 2px; }

        @media (max-width: 480px) {
            .card { padding: 30px 24px 28px; }
            h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="heading-area">
            <div class="icon-circle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                </svg>
            </div>
            <h1>Reset Password</h1>
            <p class="subtitle">Create a new password with at least eight characters and a mix of symbols.</p>
            <div class="decor-line" aria-hidden="true"><span class="decor-dot"></span></div>
        </div>

        <div class="input-group">
            <div class="decorated-input">
                <input type="password" placeholder=" " name="password" id="password" value="" autocomplete="new-password" autofocus>
                <label for="password">New Password</label>
                <button type="button" class="eye-btn" id="togglePassword" aria-label="Toggle password visibility">
                    <svg class="psw_open_eye hidden" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                        <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                    <svg class="psw_close_eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="password-rules psw_invalid_ctn hidden">
            <span id="lowerchar">Lowercase (a-z)</span>
            <span id="upperchar">Uppercase (A-Z)</span>
            <span id="numberchar">Number (0-9)</span>
            <span id="specialchar">Symbol (!@#$)</span>
            <span id="pwlength">8+ characters</span>
        </div>

        <button type="button" id="submitbtn" class="btn">
            <span class="btn-shimmer"></span>
            <span>Update Password</span>
        </button>

        <div class="message-row">
            <span class="error-message hidden" id="passwordMessage">Please complete every password rule.</span>
            <span class="error-message hidden" id="tokenMessage">This reset link is invalid or expired.</span>
            <span class="success-message hidden" id="successMessage">Password updated successfully.</span>
        </div>

        <a class="back-link" href="<?= URLROOT ?>/users/auth">← Back to <span>sign in</span></a>
    </div>

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
                const entities = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' };
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
