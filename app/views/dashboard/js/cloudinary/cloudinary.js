var AppCloudinary = new function() {
    
    this.cmsUrl = null;

    this.loadCloudinary = function (type, id) {
        $('#media-modal-body').html('');
        $('#media-modal').modal('show');
        var base = location.href.replace(/\/[^\/]+$/, '');
        socket = easyXDM.Socket({
            name: base + "/assets/dashboard/js/cloudinary/easyXDM.name.html",
            swf: base + "/assets/dashboard/js/cloudinary/easyxdm.swf",
            remote: this.cmsUrl,
            remoteHelper: "https://cloudinary.com/easyXDM.name.html",
            container: "media-modal-body",
            props: {style: {width: "100%", height: "490px"}},
            onMessage: function(message, origin){
                var json = JSON.parse(message);

                switch (type) {
                    case "menu":
                        switch (json.message) {
                            case "insert_into_post":
                                AppCloudinary.copyToClipboard(json.src);
                                //AppCloudinary.closeCloudinary();
                                swal({
                                    title: '<span style="color: green;">Image url copied to clipboard!</span>',
                                    html: '<img src="'+ json.src +'" style="width: 100%;">' + 
                                    '<input type="text" class="swal2-input" value="' + json.src + '" readonly />'
                                }).catch(swal.noop);
                                break;
                            case "done": 
                                AppCloudinary.closeCloudinary();
                                break;
                        }
                        break;
                    case "input":
                        switch (json.message) {
                            case "insert_into_post":
                                $("#"+id).val(json.src);
                                $("#"+id+"-thumbnail").attr("src", json.src);
                                AppCloudinary.closeCloudinary();
                                break;
                            case "done": 
                                AppCloudinary.closeCloudinary();
                                break;
                        }
                        break;
                    case "blog":
                        switch (json.message) {
                            case "insert_into_post":
                                //$.getScript( "https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.7.1/tinymce.min.js", function( data, textStatus, jqxhr ) {
                                    top.tinymce.activeEditor.insertContent('<img src="'+json.src+'" class="img-responsive" style="width: 100%;" />');
                                    window.parent.$("#media-modal").modal("hide");
                                //});
                                AppCloudinary.closeCloudinary();
                                break;
                            case "done": 
                                AppCloudinary.closeCloudinary();
                                break;
                        }
                        break;
                }
            },
            onReady: function() {

            }
        });
    };

    this.closeCloudinary = function() {
        $('#media-modal').modal('hide');
    };

    $("#cloudinary-menu").on('click', function(){
        AppCloudinary.loadCloudinary('menu', null);
    });
}