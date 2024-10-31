(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
     $(document).ready(function() {
        var hostnm = siteConfig.plugin_url;
    var db = firebase.database().ref('users');
    var admin_name = "";
    var admin_user = "";
    var success = false;
        $(".register-tab").on('click', function(e){
            $('#login').removeClass("in active show");
            $('.login-tab').removeClass("active");
            $('.register-tab').addClass("active");
        });

        $(".login-tab").on('click', function(e){
            $('#register').removeClass("in active show");
            $('.register-tab').removeClass("active");
            $('.login-tab').addClass("active");
        });

        $('.wp_chatee_rao_login').on('click', function(e) {
            e.preventDefault();
            var email = $('#email').val();
            var pwd = $('#pwd').val();
	        var nonce = $('#_wpnonce').val();
            if(email !== "" && pwd !== "") {
                firebase.auth().signInWithEmailAndPassword(email, pwd)
                .then((userCredential) => {
                    admin_user = userCredential.user;
                    admin_name = firebase.auth().currentUser.displayName;
                    success = true;
                    
                    $.ajax({
                        url: siteConfig.ajaxurl,
                        type: "POST",
                        data: {
                            action: "save_login_status",
                            "email": email,
                            "pwd": pwd,
                            "admin_name": admin_name,
                            _wpnonce: nonce,
                            "success": "yes"
    
                        },
                        success: function(data) {
                            if(success)
                            location.reload();
                        }
                    });
                }).catch((error) => {
                    var errorCode = error.code;
                    var errorMessage = error.message;
                   /* $.ajax({
                        url: siteConfig.ajaxurl,
                        type: "POST",
                        data: {
                            action: 'remove_firebase_login',
                            "error": error.message
                            },
                        success: function(data) {
                            location.reload();
                            }
                        
                    })*/
                    if( !success ) {
                        $('.login-error-message').html(errorMessage);
                        $('.login-error').fadeIn();
                        $.ajax({
                            url: siteConfig.ajaxurl,
                            type: "POST",
                            data: {
                                action: "save_login_status",
                                "email": email,
                                "pwd": pwd,
                                "admin_name": admin_name,
                                "success": "no"
        
                            },
                            success: function(data) {
                                if(success)
                                location.reload();
                            }
                        });
                    }
                    
                });

                
            }
        });

        $("#reset_firebase_password").on("click", function(e){
            e.preventDefault();
            var user_email = $('#email').val();
            firebase.auth().sendPasswordResetEmail( user_email)
            .then(() => {
                // Password reset email sent!
                // ..
                alert("Reset password email sent successfully");
              })
              .catch((error) => {
                var errorCode = error.code;
                var errorMessage = error.message;
                // ..
                alert(errorCode);
                alert(errorMessage);
              });
            

        });

        $('#register-user').on('click', function(e) {
            e.preventDefault();
            var nonce = $('#_wpnonce').val();
            var user_name = $('#user-name').val();
            var user_email = $('#user-email').val();
            var user_pwd = $('#user-pwd').val();
            $(".reg-error").fadeOut();
            $(".reg-success").fadeOut();
            firebase.auth().createUserWithEmailAndPassword(user_email, user_pwd).then(function (result) {
                
                var userInfo = db.push();
                $(".reg-success").fadeIn();
                userInfo.set({
                    email: user_email,
                    displayName: user_name,
                    isAdmin: true
                });


                $.ajax({
                    url: siteConfig.ajaxurl,
                    type: "POST",
                    data: {
                        action: 'register_firebase_user',
                        "email": user_email,
                        "pwd": user_pwd,
                        "name": user_name,
                        "_wpnonce": nonce,
                        },
                    success: function(data) {
                        location.reload();
                        }
                    
                })
            }).catch(function (error) {
                var errorCode = error.code;
                var errorMessage = error.message;
                $(".reg-error p").text(error.message);
                $(".reg-error").fadeIn();
            });

            
        });
    });

})( jQuery );




