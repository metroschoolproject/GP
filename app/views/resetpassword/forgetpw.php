<!DOCTYPE html>
<html lang="en">
<head>
    <title>Forgot Password - <?= APPNAME ?></title>
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
        .input-group { margin-bottom: 20px; }
        .decorated-input { position: relative; width: 100%; }
        .decorated-input input {
            width: 100%; height: 54px;
            background: var(--input-bg);
            border: 1.5px solid var(--border); border-radius: 12px;
            padding: 20px 16px 12px; font-size: 15px;
            color: var(--accent); outline: none;
            font-family: var(--body-font); font-weight: 500;
            box-shadow: 0 1px 3px rgba(44,36,32,0.04);
            transition: border-color 0.35s cubic-bezier(0.4,0,0.2,1),
                        background 0.35s cubic-bezier(0.4,0,0.2,1),
                        box-shadow 0.4s cubic-bezier(0.4,0,0.2,1);
        }
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
        .btn:disabled { cursor: wait; opacity: 0.7; transform: none; }
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
        .loading-message { display: inline-flex; align-items: center; gap: 8px; color: var(--accent); }
        .loading-message.hidden { display: none; }
        .loading-spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(109,76,91,0.2); border-top-color: var(--accent);
            border-radius: 50%; animation: spin 0.75s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .hidden { display: none; }

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
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><circle cx="12" cy="16" r="1"/>
                </svg>
            </div>
            <h1>Forgot Password</h1>
            <p class="subtitle">Enter your registered email and we'll send a reset link to your inbox.</p>
            <div class="decor-line" aria-hidden="true"><span class="decor-dot"></span></div>
        </div>

        <div class="input-group">
            <div class="decorated-input">
                <input id="resetEmail" type="email" value="" autofocus placeholder=" " autocomplete="email">
                <label for="resetEmail">Email address</label>
            </div>
        </div>

        <button type="button" class="btn" id="resetSubmitBtn">
            <span class="btn-shimmer"></span>
            <span>Send Reset Link</span>
        </button>

        <div class="message-row">
            <span class="loading-message hidden" id="loadingMessage">
                <span class="loading-spinner" aria-hidden="true"></span>
                <span>Sending...</span>
            </span>
            <span class="error-message hidden emailmessage">This email isn't registered.</span>
            <span class="error-message hidden mailmessage">Could not send reset email. Please try again later.</span>
            <span class="success-message hidden sentmessage">We sent a reset link to this email.</span>
        </div>

        <a class="back-link" href="<?= URLROOT ?>/users/auth">← Back to <span>sign in</span></a>
    </div>

    <script>
        const submitbtn = document.querySelector("#resetSubmitBtn");
        const emailmessage = document.querySelector('.emailmessage');
        const mailmessage = document.querySelector('.mailmessage');
        const sentmessage = document.querySelector('.sentmessage');
        const loadingMessage = document.querySelector('#loadingMessage');
        submitbtn.addEventListener("click", () => {
            const input = document.querySelector("#resetEmail");
            const data = { email: input.value };

            loadingMessage.classList.remove('hidden');
            emailmessage.classList.add('hidden');
            sentmessage.classList.add('hidden');
            mailmessage.classList.add('hidden');
            submitbtn.disabled = true;

            fetch("<?= URLROOT ?>/resetpassword/singleresettoken", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            })
            .then(res => res.json())
            .then(data => {
                loadingMessage.classList.add('hidden');
                if (data.e_registered == false) {
                    emailmessage.classList.remove('hidden');
                } else if (data.status === 'error') {
                    mailmessage.classList.remove('hidden');
                } else {
                    sentmessage.classList.remove('hidden');
                }
            })
            .catch(err => {
                loadingMessage.classList.add('hidden');
                mailmessage.classList.remove('hidden');
                console.error('Error sending OTP:', err);
            })
            .finally(() => { submitbtn.disabled = false; });
        });
    </script>
</body>
</html>
