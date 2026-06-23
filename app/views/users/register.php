<?php
    require_once APPROOT . '/views/layouts/header.php';
        
    //     echo $_SESSION['session_uid'];
    //    echo  $_SESSION['session_email'];
?>


<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <meta name="description" content="Login">
    <?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php $dashboardCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/app.css">

</head>
<style>
    #emailvalid {
        display: none;
    }

    .psw_invalid_hide {
        display: none;
    }

    .psw_open_eye {
        display: none;
    }

    /* Hide browser's native password reveal eye (Chrome, Edge, Safari) */
    input[type="password"]::-webkit-credentials-auto-fill-button,
    input[type="password"]::-ms-reveal { display: none !important; }
</style>

<body>

    <section class="auth-shell w-full h-[100vh] flex justify-center items-center">
        <div class="auth-card w-[600px] min-h-[500px]">
            <h3 class="auth-title text-2xl text-center mb-4">Register</h3>
             <div class=" mt-8">    
                <input type="text" placeholder="username" name="username" value=""
                    class="auth-field w-[475px] border border-1 p-5 mx-2 error_alert_border" autofocus>
            </div>

            <div class="mt-3">        
                <input type="email" placeholder="email" name="email" value=""
                    class="auth-field w-[475px] border border-1 p-5 mx-2 error_alert_border">
                <span class="emailvalid form-error form-helper" id="emailvalid">Email
                    is already exist.</span>
            </div>

            <div class="relative mt-3">            
                <input type="password" placeholder="password" name="password" id="password" value="1Aa@23456"
                    class="auth-field w-[475px] border border-1 p-5 mx-2 error_alert_border">
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
                <div class="psw_invalid_ctn psw_invalid_hide px-3  mb-5">
                    <span class="psw_invalid_txt form-error form-helper" id="specialchar">Specialcase
                        character</span>
                    <br>
                    <span class="psw_invalid_txt form-error form-helper" id="upperchar">Uppercase
                        character</span>
                    <br>
                    <span class="psw_invalid_txt form-error form-helper" id="lowerchar">Lowercase
                        character</span>
                    <br>
                    <span class="psw_invalid_txt form-error form-helper" id="numberchar">Numbers</span>
                    <br>
                    <span class="psw_invalid_txt form-error form-helper" id="pwlength">password
                        must be 8</span>

                </div>
            </div>

            <div class="relative mt-3">                
                <input type="password" placeholder="comfirm password" name="compassword"
                        id="compassword" value="1Aa@23456" class="auth-field w-[475px] border border-1 p-5 mx-2 error_alert_border">
                <span class="absolute right-7 top-[12px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-6 psw_open_eye icon-eye">
                        <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <!-- close eye  -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-6 psw_close_eye icon-eye">
                        <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </span>

                <span id="matchornot" class="px-3 form-error form-helper"></span>
            </div>

            <div class="w-full flex justify-center">
                <button type="button" class="auth-button w-[469px] h-11 registerregister_btn">Register</button>
            </div>
            
            <div class="mt-10 form-helper flex justify-center items-center">
                <span>Don't you have an account? <a class="auth-link" href=""> Login</a></span>
            </div>
       
        </div>
    </section>
    

    <script>


        const password = document.getElementById("password");
        const compassword = document.getElementById("compassword");
        const specialchar = document.getElementById("specialchar");
        const upperchar = document.getElementById("upperchar");
        const lowerchar = document.getElementById("lowerchar");
        const numberchar = document.getElementById("numberchar");
        const pwlength = document.getElementById("pwlength");
        const invalid = document.getElementById("invalidinput");
        const psw_invalid_ctn = document.querySelector(".psw_invalid_ctn");
        const matchornot = document.getElementById("matchornot");
        let isStrongPw = false;



        // password validate
        password.addEventListener("input", () => {
            const pw = password.value;
            validatePassword(pw);
            psw_invalid_ctn.classList.remove("psw_invalid_hide");
        });

        function validatePassword(pw) {
            const rules = [
                { test: /[a-z]/, element: lowerchar },
                { test: /[A-Z]/, element: upperchar },
                { test: /[0-9]/, element: numberchar },
                { test: /[~!@#$%^&*()]/, element: specialchar },
                { test: /.{8,}/, element: pwlength }
            ];

            const invalidChars = /[`\-\+<>?|]/;
            if (invalidChars.test(pw)) {
                alert("Input contains invalid characters.");
                console.log("Invalid characters detected.");
                return;
            }

            rules.forEach(rule => {
                const pass = rule.test.test(pw);
                rule.element.style.color = pass ? "green" : "red";
                if (!pass) {
                    isStrongPw = false;
                } else{
                    isStrongPw = true;
                }
            });
        }



        // password match ?
        compassword.addEventListener("input", () => {
            checkPasswordMatch();

        });


        function checkPasswordMatch() {
            if (password.value === compassword.value) {
                matchornot.textContent = "password match!";
                matchornot.style.color = "green";
                password.style.border = "1px solid rgb(120, 120, 196)";
                compassword.style.border = "1px solid rgb(120, 120, 196)";
                matchornot.style.marginBottom = "10px";

                return true;
            } else {
                matchornot.textContent = "password doesn't match!!";
                matchornot.style.color = "red";
                compassword.style.border = "1px solid red";
                matchornot.style.marginBottom = "10px";
                return false;
            }
        }

        // show and hide password
        document.addEventListener("DOMContentLoaded", () => {
            const psw_open_eye = document.querySelectorAll('.psw_open_eye');
            const psw_close_eye = document.querySelectorAll('.psw_close_eye');
            const passwordFields = [
                document.getElementById("password"),
                document.getElementById("compassword")
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
            const inputs = document.getElementsByTagName('input');
            const register_btn = document.querySelector(".registerregister_btn");
            const emailvalid = document.querySelector('.emailvalid');

            register_btn.addEventListener("click", () => {
                for (let i = 0; i < inputs.length; i++) {
                    if (!inputs[i].value.trim() == "") {
                        inputs[i].style.border = "1px solid rgb(120, 120, 196)";
                    } else {
                        inputs[i].style.border = "1px solid red";
                    }
                }
                if (isStrongPw && checkPasswordMatch()) {
                    psw_invalid_ctn.classList.add("psw_invalid_hide");

                    // fetch api
                    const data = {
                        username: safeInput("username"),
                        email: safeInput("email"),
                        password: safeInput("password"),
                        compassword: safeInput("compassword"),
                    };

                    fetch("<?= URLROOT ?>/users/register", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(data),
                    })
                        .then(res => res.json())
                        .then(res => {
                            if (res.email == true) {
                                console.log('email true')
                                emailvalid.style.display = "block";
                            } else if (res.status == 'success') {
                                console.log('res status success')
                                window.location.href = "<?= URLROOT ?>/" + res.redirect;
                            }
                        }

                        )
                        .catch(err => console.error("Fetch error:", err));
                } else {
                    console.log("It's not strong pw or pw doesn't match")
                }

                    

            });

        })


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


    </script>
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>

</html>
