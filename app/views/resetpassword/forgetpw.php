<?php require_once APPROOT . '/views/layouts/header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
</head>
<body>
    <section class="w-full h-[100vh] bg-gray-200 flex justify-center items-center">
        <div class="w-[600px] min-h-[400px] bg-gray-100 rounded-lg px-14 py-10">
            <h3 class="text-cyan-800 text-2xl mb-4">Forget Password</h3>
            <span class="text-gray-500">If that email is registered, we sent a reset link. </span>
            
            <div class="w-full flex justify-center mt-10">
                <input type="email"  class="w-[500px] h-12 border border-1 rounded-lg p-5 m-2 focus:outline focus:border focus:border-cyan-200 p-5" value="" autofocus placeholder="email">
            </div>
            <span class="text-xs text-red-500 ml-2 hidden emailmessage">Email isn't registered.</span>

            <div class="w-full flex justify-center mt-5 ">
                <button type="button" class="w-[469px] h-11 rounded-lg text-white bg-cyan-800">Submit</button>
            </div>

            <div class="w-full flex justify-center mt-10 ">
                <span class="text-md text-green-600 hidden sentmessage">We already sent reset link to this email.</span>
            </div>

        </div>
    </section>
    <script>
        const submitbtn = document.querySelector("button");
        const emailmessage = document.querySelector('.emailmessage');
        const sentmessage = document.querySelector('.sentmessage');
        submitbtn.addEventListener("click",()=>{
            const input = document.querySelector("input");
            const value = input.value;
                       const data = {email : value};

            fetch("<?= URLROOT ?>/resetpassword/singleresettoken",{
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            })
            .then(res => res.json())
            .then(data => {
                if(data.e_registered == false){
                    emailmessage.classList.replace('hidden', 'show');
                }else{
                    sentmessage.classList.replace('hidden','show');
                    emailmessage.classList.replace('show', 'hidden');

                }
            })
            .catch(err => {
                console.error('Error sending OTP:', err);
            });

        })


        

    </script>
</body>
</html>
