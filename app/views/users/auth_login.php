

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
</style>

<body>

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
                            lock_warning_bar.classList.replace('hidden','show')
                            lock_until_time.innerHTML = res.lockedUntil.date
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
                if(res.loginfailover == true){
                    login_warning_bar.classList.replace('hidden','show');
                    login_warning_bar.innerHTML = "We sent Login Fail Alert to this email because you try to attemp over 5 times"
                    console.log('hh')
                    pwvalid.style.display = 'block';
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
