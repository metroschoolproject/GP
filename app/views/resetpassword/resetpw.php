<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <title>Reset Password</title>
    <meta name="description" content="Reset Password">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
</head>
<style>
    body {
        font-family: "Playfair Display", serif;
        font-optical-sizing: auto;
        font-weight: weight;
        font-style: normal;
        padding: 0;
        margin: 0;

    }

    .psw_invalid_hide {
        display: none;
    }

    .psw_open_eye {
        display: none;
    }
</style>

<body>

    <section class="w-full h-[100vh] bg-gray-200 flex justify-center items-center">
        <!-- <div class="w-[98%] h-10 bg-gray-500 text-white text-sm absolute top-5  flex justify-center items-center rounded-md warning-bar ">
            <span class=""> You already used this token.</span>
        </div> -->

        <div class="w-[600px] min-h-[400px] bg-gray-100 rounded-lg px-14 py-10">
            <h3 class="text-cyan-800 text-2xl mb-4">Reset Password</h3>
            <span class="text-gray-500">If that email is registered, we sent a reset link. </span>

            <div class="relative mt-8">
                <input type="password"  placeholder="new password" name="password" id="password" value="" 
                    class="w-[475px] h-12 border border-1 rounded-lg p-5 mx-2 focus:outline focus:border focus:border-cyan-200 p-5 ">
                <span class="absolute right-7 top-[12px]">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-6 psw_open_eye"
                        style="width: 15px; ">
                        <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="size-6 psw_close_eye"
                        style="width: 15px;">
                        <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </span>
                <br>
                <div class="psw_invalid_ctn psw_invalid_hide px-3 mt-[-20px] mb-5">
                    <span class="psw_invalid_txt " id="specialchar"
                        style="color: red; font-size: 13px;">Specialcase
                        character</span>
                    <br>
                    <span class="psw_invalid_txt" id="upperchar"
                        style="color: red; font-size: 13px;">Uppercase
                        character</span>
                    <br>
                    <span class="psw_invalid_txt" id="lowerchar"
                        style="color: red; font-size: 13px;">Lowercase
                        character</span>
                    <br>
                    <span class="psw_invalid_txt" id="numberchar"
                        style="color: red; font-size: 13px;">Numbers</span>
                    <br>
                    <span class="psw_invalid_txt" id="pwlength"
                        style="color: red; font-size: 13px;">password
                        must be 8</span>
                </div>
            </div>

            <div class="w-full flex justify-center mt-2">
                <button type="button" id="submitbtn" class="w-[469px] h-11 rounded-lg text-white bg-cyan-800">Submit</button>
            </div>

        </div>
    </section>
    <input type="hidden" id="token" name="token" value="<?= htmlspecialchars($token) ?>">
    <input type="hidden" id="email" name="email" value="<?= htmlspecialchars($email) ?>">


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





        // show and hide password
        document.addEventListener("DOMContentLoaded", () => {
            const psw_open_eye = document.querySelectorAll('.psw_open_eye');
            const psw_close_eye = document.querySelectorAll('.psw_close_eye');
            const passwordFields = [
                document.getElementById("password"),
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
            const submitbtn = document.querySelector("#submitbtn");
            const token = document.getElementById('token');
            const email = document.getElementById('email');

            submitbtn.addEventListener("click", () => {
                            var inspired = false;

                console.log('hi')
                for (let i = 0; i < inputs.length; i++) {
                    if (!inputs[i].value.trim() == "") {
                        inputs[i].style.border = "1px solid rgb(120, 120, 196)";
                    } else {
                        inputs[i].style.border = "1px solid red";
                    }
                }
                if (isStrongPw) {
                    psw_invalid_ctn.classList.add("psw_invalid_hide");

                    // fetch api
                    const data = {
                        password: safeInput("password"),
                        token : token.value,
                        email : email.value

                    };
      

                fetch("<?= URLROOT ?>/resetpassword/setnewpassword", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(data),
                })
                        .then(res => res.json())
                        .then(res => {
                          if(res.status == "inspired"){
                            console.log(res );
                            console.log("Already use this token");
                            inspired = true;
                          }else{
                            console.log("Password Update");
                          }

                    
                        })
                        .catch(err => console.error("Fetch error:", err));
                } else {
                    console.log("It's not strong pw or pw doesn't match")
                }

                if(inspired == true){
                    submitbtn.classList.replace("bg-cyan-800","bg-green-800");

                }else{
                    submitbtn.classList.replace("bg-cyan-800","bg-blue-800");

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


</body>

</html>
