<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token(">
    <title>Sign In | CRM</title>
    <!-- CSS files -->
    <link href="dist/css/tabler.min.css" rel="stylesheet" />
    <link href="dist/css/tabler-flags.min.css" rel="stylesheet" />
    <link href="dist/css/tabler-payments.min.css" rel="stylesheet" />
    <link href="dist/css/tabler-vendors.min.css" rel="stylesheet" />
    <link href="dist/css/demo.min.css" rel="stylesheet" />
    <!-- Toaster CSS files -->
    <link rel="stylesheet" href="dist/toast/webToast.min.css" />
    <!-- Validatta CSS files -->
    <link rel="stylesheet" href="dist/validetta/validetta.min.css" />

    <style>
@importurl('https://rsms.me/inter/inter.css');

    :root {
        --tblr-font-sans-serif: Inter, -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }
    </style>
</head>

<body class=" border-top-wide border-primary d-flex flex-column">
    <script src="dist/js/demo-theme.min.js"></script>
    <div class="page page-center">
        <div class="container container-tight py-4">
            <!-- <div class="text-center mb-4">
                <a href="." class="navbar-brand navbar-brand-autodark"><img src="static/logo.svg" height="36"
                        alt=""></a>
            </div> -->
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Login to your account</h2>
                    <form id="signInForm" method="post">
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input name="email" id="email" type="text" data-validetta="required,email" class="form-control" placeholder="your@email.com" autocomplete="off"/>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">
                                Password
                                <!-- <span class="form-label-description">
                                    <a href="forgot-password.html">I forgot password</a>
                                </span> -->
                            </label>
                            <div class="input-group input-group-flat">
                                <input name="password" id="password" type="password" data-validetta="required" class="form-control" placeholder="Your password" autocomplete="off">
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="Show password" data-bs-toggle="tooltip">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <circle cx="12" cy="12" r="2" />
                                            <path
                                                d="M22 12c-2.667 4.667 -6 7 -10 7s-7.333 -2.333 -10 -7c2.667 -4.667 6 -7 10 -7s7.333 2.333 10 7" />
                                        </svg>
                                    </a>
                                </span>
                            </div>
                        </div>
                        <div class="form-footer">
                            <!-- <button onclick="signIn()" type="submit" class="btn btn-primary w-100">Sign in</button> -->
                            <button id="btnSubmit" class="btn btn-primary w-100">Sign in</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="dist/js/tabler.min.js" defer></script>
    <script src="dist/js/demo.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="dist/toast/webToast.min.js'"></script>
    <script src="dist/validetta/validetta.min.js'"></script>
<script type="text/javascript">

$(document).ready(function() {
    $('#signInForm').validetta({
        realTime: true,
        display: 'inline',
        errorTemplateClass: 'validetta-inline',
        onValid: function (event) {
            event.preventDefault();
            $.ajax({
                url: "login.singUp",
                data: $("#signInForm").serialize(),
                dataType: 'json',
                method: 'post'
            })
            .done(function (data) {  
                if (data.status == "success") {  
                    window.location.href = data.redirect; 
                } else {
                    webToast.Danger({status:'Failed',message: data.message});
                }                    
            })
            .fail(function (xhr, textStatus) {
                if (xhr.responseJSON && xhr.status === 400) {
                    webToast.Danger({status:'Failed',message: xhr.responseJSON.message});
                } else {
                    webToast.Danger({status:'Failed',message: "Something went wrong (" + xhr.status + ")"});
                }
            })
        }
    });
});
</script>

</body>

</html>