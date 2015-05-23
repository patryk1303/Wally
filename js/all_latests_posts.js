function getAllLatestsPosts(siteURL) {
    $.ajax({
        method: 'GET',
        url: siteURL
    })
    .done(function(response) {
        var posts = JSON.parse(response),
            $element = $('#allLatestsPosts'),
            html = '';
            
        if(posts.length == 0) {
            $element.html('Brak wiadomości');
            return;
        }
    });
}