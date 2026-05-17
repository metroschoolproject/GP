
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <meta name="description" content="Login">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <?php $dashboardCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/app.css">

</head>
<body>
    <section class="w-full h-[100vh] bg-gray-200 flex justify-center items-center">
        <div class="w-[600px] h-[500px] bg-gray-100 rounded-lg px-10 py-5">
            <h3 class="text-cyan-800 text-2xl mb-4">OTP Verification</h3>
            <span class="text-gray-500">Please enter the OTP [One Time Password] sent to your
                registered email number to commplete your verification. </span>
            
            <div class="w-full flex justify-center mt-10">
                <input type="text" maxlength="1" class="w-14 h-14 shadow rounded-lg p-5 m-2 focus:outline focus:border focus:border-cyan-200" value="" >
                <input type="text" maxlength="1" class="w-14 h-14 shadow rounded-lg p-5 m-2 focus:outline focus:border focus:border-cyan-200"  value="">
                <input type="text" maxlength="1" class="w-14 h-14 shadow rounded-lg p-5 m-2 focus:outline focus:border focus:border-cyan-200" value="">
                <input type="text" maxlength="1" class="w-14 h-14 shadow rounded-lg p-5 m-2 focus:outline focus:border focus:border-cyan-200" value="">
                <input type="text" maxlength="1" class="w-14 h-14 shadow rounded-lg p-5 m-2 focus:outline focus:border focus:border-cyan-200" value="">
                <input type="text" maxlength="1" class="w-14 h-14 shadow rounded-lg p-5 m-2 focus:outline focus:border focus:border-cyan-200" value="">
            </div>

            <div class="w-full text-center mt-5">
                <p class="text-cyan-800"><span class="min"> </span> <span class="sec"></span></p>
            </div>
            <div class="w-full text-center mt-24">
                <span class="text-xs text-red-500"><span class="atm_time"></span></span>
                <p id="resentotp" class="text-gray-500 text-sm mt-2">Resent OTP code</p>

            </div>
        </div>
    </section>

    <script>
        const inputs = document.querySelectorAll("input");
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
            let count = 1 * 60;
            const min = document.querySelector('.min');
            const sec = document.querySelector('.sec');

            const timer = setInterval(() => {
            let minutes = Math.floor(count / 60);
            let seconds = count % 60;

            min.innerHTML = minutes + " : ";
            sec.innerHTML = seconds < 10 ? '0' + seconds : seconds;

            if(seconds == 00){
                min.innerHTML = "Expired OTP";
                sec.innerHTML = " ";
            }

            if (count <= 0) clearInterval(timer);
                count--;
            }, 1000);


        }
        window.onload = setTimer;

        const resentotp = document.querySelector("#resentotp");
        resentotp.addEventListener('click',()=>{
            window.location.href = window.location.href;
        })
    </script>
</body>
</html>
