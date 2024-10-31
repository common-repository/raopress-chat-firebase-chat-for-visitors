jQuery(document).ready(function ($) {
    $('#prime').click(function() {
        toggleFab();
      });

    function toggleFab() {
        $('.prime').toggleClass('zmdi-chat-open');
        $('.prime').toggleClass('zmdi-chat-close');
       /* $('.prime').toggleClass('is-active');
        $('.prime').toggleClass('is-visible');*/
        $('#prime').toggleClass('is-float');
        
        $('.fab').toggleClass('is-visible');
        
        if($('.prime').hasClass('zmdi-chat-open')) {
            $('.prime img').attr('src',siteConfig.plugin_url+"images/chat-icon.png");
        } else {
            $('.prime img').attr('src',siteConfig.plugin_url+"images/close.png");
        }
        $('.chat').toggleClass('is-visible');
    
    }
});