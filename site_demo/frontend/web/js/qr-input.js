(function() {
    document.addEventListener("keyup", function(event) {
        if (event.key == "F7") {
            var input = $('#selfserviceuserqrinput-code');
            input.focus();
            input.val('');
        }
    });
})();
