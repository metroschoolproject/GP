

<head>
    <title>Login</title>
    <meta name="description" content="Login">
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php $dashboardCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/app.css">

</head>
<style>
    #emailvalid,
    #pwvalid,
    #checkpw {
        display: none;
    }

    .psw_invalid_hide {
        display: none;
    }

    .psw_open_eye {
        display: none;
    }

    .auth-home-logo {
        position: fixed;
        top: 22px;
        left: 22px;
        z-index: 55;
        display: grid;
        width: 74px;
        height: 74px;
        place-items: center;
        overflow: hidden;
        transition: transform 180ms ease;
    }

    .auth-home-logo:hover {
        transform: translateY(-2px);
    }

    .auth-home-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (max-width: 640px) {
        .auth-home-logo {
            top: 12px;
            left: 12px;
            width: 58px;
            height: 58px;
        }
    }

    .attempt-popup {
        position: fixed;
        top: 22px;
        right: 22px;
        z-index: 50;
        width: min(calc(100vw - 32px), 440px);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid rgba(185, 75, 75, 0.22);
        border-radius: 12px;
        background: rgba(252, 248, 245, 0.97);
        box-shadow: 0 18px 44px rgba(74, 52, 47, 0.16);
        opacity: 0;
        pointer-events: none;
        transform: translateY(-14px) scale(0.96);
        transition: opacity 180ms ease;
    }

    .attempt-popup.show {
        opacity: 1;
        pointer-events: auto;
        animation: attemptPopupIn 520ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    .attempt-popup.is-locked {
        border-color: rgba(185, 75, 75, 0.34);
        background: rgba(255, 242, 242, 0.98);
    }

    .attempt-popup-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: rgba(185, 75, 75, 0.12);
        color: #b94b4b;
        font-weight: 800;
        transform: scale(0.82);
        transition: transform 420ms cubic-bezier(0.34, 1.56, 0.64, 1) 80ms;
    }

    .attempt-popup-icon svg {
        width: 20px;
        height: 20px;
        stroke-width: 2.25;
    }

    .attempt-popup.show .attempt-popup-icon {
        transform: scale(1);
        animation: attemptIconPulse 620ms cubic-bezier(0.34, 1.56, 0.64, 1) 80ms both;
    }

    .attempt-popup-title {
        margin: 0;
        color: #4a342f;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.15;
    }

    .attempt-popup-text {
        margin: 3px 0 0;
        color: #7b5c69;
        font-size: 12px;
        font-weight: 500;
        line-height: 1.45;
    }

    @keyframes attemptPopupIn {
        0% { opacity: 0; transform: translateY(-18px) scale(0.94); filter: blur(4px); }
        60% { opacity: 1; transform: translateY(3px) scale(1.01); filter: blur(0); }
        100% { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
    }

    @keyframes attemptIconPulse {
        0% { transform: scale(0.72) rotate(-8deg); }
        64% { transform: scale(1.12) rotate(3deg); }
        100% { transform: scale(1) rotate(0); }
    }

    @media (max-width: 640px) {
        .attempt-popup {
            left: 16px;
            right: 16px;
            top: 14px;
            width: auto;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .attempt-popup,
        .attempt-popup-icon {
            transition: opacity 160ms ease;
        }
    }
</style>

<body>
    <a class="auth-home-logo" href="<?= URLROOT ?>/main/index" aria-label="Go to Golden Promise home">
        <img src="<?= URLROOT ?>/public/images/home/gp_logo.png" alt="Golden Promise logo">
    </a>

    <div class="attempt-popup" id="attemptPopup" role="status" aria-live="polite" aria-atomic="true">
        <div class="attempt-popup-icon" id="attemptPopupIcon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        </div>
        <div>
            <p class="attempt-popup-title" id="attemptPopupTitle">Wrong password</p>
            <p class="attempt-popup-text" id="attemptPopupText">Please try again.</p>
        </div>
    </div>

    <section class="auth-shell w-full h-[100vh] flex justify-center items-center">
        <div class="w-[98%] h-10 bg-yellow-500 text-white text-sm absolute top-5  flex justify-center items-center rounded-md warning-bar hidden">
            <span class=""> Warning!! Your account is locked until <span class="lock-until-time"></span>.</span>
        </div>

        <div class="w-[98%] h-10 bg-red-100 absolute top-5  flex justify-center items-center rounded-md accountnotfound-warning-bar hidden">
            <span class="text-cyan-800 text-sm">Your account is not found.</span>
        </div>
        <div class="auth-card w-[600px] min-h-[450px]">
            <h3 class="auth-title text-2xl text-center mb-4">Log In</h3>


                        <div class="mt-8">
                            <input type="email" placeholder="email" name="email" value=""
                                class="auth-field w-[475px] border border-1 p-5 mx-2 error_alert_border">
                            <br>
                            <span class="px-3 emailvalid form-error form-helper" id="emailvalid">
                                email is incorrect.</span>
                        </div>
                        <div class="relative mt-5">

                                <input type="password" placeholder="password" name="password" id="password"
                                    value="1Aa@23456" class="auth-field w-[475px] border border-1 p-5 mx-2 error_alert_border">
                                <span class="absolute right-7 top-[12px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6 psw_open_eye icon-eye">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6 psw_close_eye icon-eye">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </span>

                            <div class="text-sm text-cyan-800 flex justify-between px-3 mt-2">

                                <span class="pwvalid form-error form-helper" id="pwvalid">
                                password is incorrect.</span>
                                <span id="forgetpwbtn">Forget password</span>
                            </div>

                        </div>

                        <div class="w-full flex justify-center mt-5">
                            <button type="button" class="auth-button w-[469px] h-11 loginregister_btn">Login</button>
                        </div>
              



                <div class="mt-20 form-helper flex justify-center items-center">
                    <span>Don't you have an account? <a class="auth-link" href="">Register</a></span>
                </div>
        </div>
    </section>
    <script>


        const password = document.getElementById("password");

        // show and hide password
        document.addEventListener("DOMContentLoaded", () => {
            const psw_open_eye = document.querySelectorAll('.psw_open_eye');
            const psw_close_eye = document.querySelectorAll('.psw_close_eye');
            const passwordFields = [
                document.getElementById("password")
            ];

            passwordFields.forEach((input, idx) => {
                const openEye = psw_open_eye[idx];
                const closeEye = psw_close_eye[idx];

                // Show password
                closeEye.addEventListener("click", () => {
                    input.setAttribute("type", "text");
                    closeEye.style.display = "none";
                    openEye.style.display = "inline";
                });

                // Hide password
                openEye.addEventListener("click", () => {
                    input.setAttribute("type", "password");
                    openEye.style.display = "none";
                    closeEye.style.display = "inline";
                });
            });
        });



        // all rules are completed ?
        document.addEventListener("DOMContentLoaded", () => {
            var inputs = document.getElementsByTagName('input');
            let loginregister_btn = document.querySelector(".loginregister_btn");
            let emailvalid = document.querySelector('.emailvalid');
            const lock_warning_bar = document.querySelector('.warning-bar');
            const lock_until_time = document.querySelector('.lock-until-time');

            const accountnotfound_warning_bar  = document.querySelector('.accountnotfound-warning-bar');
            loginregister_btn.addEventListener("click", () => {
                for (var i = 0; i < inputs.length; i++) {
                    if (!inputs[i].value.trim() == "") {
                        inputs[i].style.border = "1px solid rgb(120, 120, 196)";
                    } else {
                        inputs[i].style.border = "1px solid red";
                    }

                }

                // fetch api
                const data = {
                    email: safeInput("email"),
                };
            

                    fetch("<?= URLROOT ?>/users/login", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data),
                })
                    .then(res => res.json())
                    .then(res => {
                        // If no lock 
                        if (res.challenge == false) {
                            emailvalid.style.display = 'block';
                        } 
                        else if (res.status == 'success') {
                            console.log(res);
                            const ccode = res.challenge.challenge;
                            handleLogin(ccode);
                        }

                        // If account locked 
                        if(res.status == 'lock'){
                            console.log(res);
                            const lockedUntil = res.lockedUntil && res.lockedUntil.date ? res.lockedUntil.date : '';
                            const lockMessage = accountLockMessage(lockedUntil);
                            showAttemptPopup(0, res.max_attempts || 3, true, lockMessage);
                            lock_warning_bar.classList.add('hidden');
                            lock_warning_bar.classList.remove('show');
                            lock_warning_bar.innerHTML = '';
                        }

                        // account not found 
                        if(res.status == 'accountnotfound'){
                            console.log(res);
                            accountnotfound_warning_bar.classList.replace('hidden','show');
                        }
                    }

                    )
                    .catch(err => console.error("Fetch error:", err));

            });

        })

        function accountLockMessage(lockedUntil) {
            const formattedUntil = formatLockTime(lockedUntil);
            return lockedUntil
                ? `Too many wrong attempts. Try again after ${formattedUntil}, or reset your password.`
                : 'Too many wrong attempts. Please wait a little, or reset your password.';
        }

        function formatLockTime(value) {
            if (!value) return '';
            const raw = String(value).trim();
            const match = raw.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
            if (!match) return raw;

            const year = Number(match[1]);
            const month = Number(match[2]);
            const day = Number(match[3]);
            let hour = Number(match[4]);
            const minute = match[5];
            const ampm = hour >= 12 ? 'PM' : 'AM';
            hour = hour % 12 || 12;

            return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')} ${hour}:${minute} ${ampm}`;
        }

        function showAttemptPopup(remainingAttempts, maxAttempts, isLocked = false, lockedMessage = '') {
            const popup = document.getElementById('attemptPopup');
            const icon = document.getElementById('attemptPopupIcon');
            const title = document.getElementById('attemptPopupTitle');
            const text = document.getElementById('attemptPopupText');
            if (!popup || !icon || !title || !text) return;

            const remaining = Math.max(0, Number(remainingAttempts) || 0);
            const max = Math.max(1, Number(maxAttempts) || 3);
            const attempt = Math.min(max, Math.max(1, max - remaining));
            const warningIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>';
            const lockIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/><path d="M12 15v2"/></svg>';

            popup.classList.toggle('is-locked', isLocked || remaining === 0);
            icon.innerHTML = isLocked || remaining === 0 ? lockIcon : warningIcon;
            title.textContent = isLocked || remaining === 0
                ? 'Account locked'
                : 'Wrong password';
            text.textContent = isLocked || remaining === 0
                ? (lockedMessage || 'Too many wrong attempts. Please wait a little, or reset your password.')
                : `Please try again. Attempt ${attempt} of ${max}.`;

            popup.classList.remove('show');
            void popup.offsetWidth;
            popup.classList.add('show');

            clearTimeout(showAttemptPopup.hideTimer);
            showAttemptPopup.hideTimer = setTimeout(() => {
                popup.classList.remove('show');
            }, 3800);
        }

        // Handle Login 
        async function handleLogin(ccode){
            const password = safeInput("password");
            const pw_sha = await sha256(password);
            const challenge = pw_sha + ccode;
            const response = await sha256(challenge);
            const pwvalid = document.querySelector('#pwvalid');
            const login_warning_bar = document.querySelector('.warning-bar');


            const data = {
                email: safeInput("email"),
                pw_sha: pw_sha,        
                res_code: response
            };

            fetch("<?= URLROOT ?>/users/verifyChallenge", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            })
            .then(res => res.json())
            .then(res => {
                if(res.status == 'lock' || res.loginfailover == true){
                    const lockedUntil = res.lockedUntil && res.lockedUntil.date ? res.lockedUntil.date : '';
                    const lockMessage = accountLockMessage(lockedUntil);
                    showAttemptPopup(0, res.max_attempts || 3, true, lockMessage);
                    login_warning_bar.classList.add('hidden');
                    login_warning_bar.classList.remove('show');
                    login_warning_bar.innerHTML = '';
                    pwvalid.style.display = 'none';
                    pwvalid.textContent = '';
                    return;
                }
                if(res.loginfailnotyet == true || res.pwd === false || res.status === false){
                    const remainingAttempts = Number.isFinite(Number(res.remaining_attempts))
                        ? Number(res.remaining_attempts)
                        : Math.max(0, Number(res.max_attempts || 3) - Number(res.attempt_count || 0));
                    showAttemptPopup(remainingAttempts, res.max_attempts || 3, false);
                    const attemptText = res.attempt_count && res.max_attempts
                        ? ` Attempt ${res.attempt_count} of ${res.max_attempts}.`
                        : '';
                    pwvalid.style.display = 'block';
                    pwvalid.textContent = `Password is incorrect.${attemptText}`;
                    return;
                }
                if(res.status == true){
                    window.location.href = "<?= URLROOT ?>/otps/otp";
                    console.log(window.location.href)

                }
             
                console.log(res);
            })
            .catch(err => console.error("Fetch error:", err));

        }


        // input sanitization
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

        // Web Crypto 
        async function sha256(message) {
            const msgBuffer = new TextEncoder().encode(message);
            const hashBuffer = await crypto.subtle.digest("SHA-256", msgBuffer);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        }



        // forget password 
        const forgetpwbtn = document.querySelector('#forgetpwbtn');
        forgetpwbtn.addEventListener('click',()=>{
          window.location.href = '<?= URLROOT ?>/resetpassword/singleresettoken';  
        })

    </script>

</body>

</html> 
