var AppMedia = new function() {

    this.managerUrl = null;
    this.getFolderUrl = null;
    this.addFolderUrl = null;
    this.deleteMediaUrl = null;

    // Load the Local CMS Window
    this.loadMedia = function (type, target) {
    	$('#media-modal-body').html('');
        switch (type) {
            case "file":
              $("#media-modal-body").html('<iframe id="appmedia" width="100%" height="475px" frameborder="0" marginheight="0" marginwidth="0" src="'+
                  this.managerUrl+
                  '?source=menu"></iframe>'
              );
              break;
            case "menu":
                $("#media-modal-body").html('<iframe id="appmedia" width="100%" height="475px" frameborder="0" marginheight="0" marginwidth="0" src="'+
                    this.managerUrl+
                    '?source=menu"></iframe>'
                );
                break;
            case "input":
                $("#media-modal-body").html('<iframe id="appmedia" width="100%" height="475px" frameborder="0" marginheight="0" marginwidth="0" src="'+
                    this.managerUrl+
                    '?source=input&target='+target+'"></iframe>'
                );
                break;
            case "blog":
                $("#media-modal-body").html('<iframe id="appmedia" width="100%" height="475px" frameborder="0" marginheight="0" marginwidth="0" src="'+
                    this.managerUrl+
                    '?source=blog&target='+target+'"></iframe>'
                );
                break;
            case "file_uploaded":
                $("#media-modal-body").html('<iframe id="appmedia" width="100%" height="475px" frameborder="0" marginheight="0" marginwidth="0" src="'+
                    this.managerUrl+
                    '?source=file_uploaded&target='+target+'"></iframe>'
                );
                break;
            case "blog_featured":
                $("#media-modal-body").html('<iframe id="appmedia" width="100%" height="475px" frameborder="0" marginheight="0" marginwidth="0" src="'+
                    this.managerUrl+
                    '?source=blog_featured&target='+target+'"></iframe>'
                );
                break;
            case "seo_featured":
                $("#media-modal-body").html('<iframe id="appmedia" width="100%" height="475px" frameborder="0" marginheight="0" marginwidth="0" src="'+
                    this.managerUrl+
                    '?source=blog_featured&target='+target+'"></iframe>'
                );
                break;

        }
        $('#media-modal').modal('show');
    };

    this.createFolder = function(currentFolder) {
        var addFolderUrl = this.addFolderUrl;
        swal({
            title: 'Create a New Folder',
            input: 'text',
            showCancelButton: true,
            confirmButtonText: 'Submit',
            showLoaderOnConfirm: true,
            preConfirm: function (newFolderName) {
                return new Promise(function (resolve, reject) {
                    AppCSRF.csrfAjax(
                        addFolderUrl,
                        {current_folder: currentFolder, new_folder_name: newFolderName},
                        function(result){
                            parsed = jQuery.parseJSON(result);
                            if (parsed.result == "error") {
                                var msg = "";
                                $.each(parsed.data, function(i, item) {
                                    msg = msg + "<br />" + item;
                                })
                                reject(msg);
                            }else if(parsed.result == "success"){
                                resolve();
                            }else{
                                reject('An unknown error occured.');
                            }

                        }
                    );
                })
            },
            allowOutsideClick: false
        }).then(function (newFolderName) {
            AppMedia.getFolder(currentFolder + '/' + newFolderName);
            swal({
                type: 'success',
                title: 'New folder created successfully!',
                html: currentFolder + '/' + newFolderName
            })
        }).catch(swal.noop);
    }

    this.deleteMedia = function(currentFolder, currentFile){
        var deleteMediaUrl = this.deleteMediaUrl;
        swal({
            title: 'Are you sure you want to delete this file?',
            text: "You won't be able to revert this!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            confirmButtonClass: 'btn btn-success',
            cancelButtonClass: 'btn btn-danger',
            buttonsStyling: true,
            preConfirm: function (newFolderName) {
                return new Promise(function (resolve, reject) {
                    AppCSRF.csrfAjax(
                        deleteMediaUrl,
                        {current_folder: currentFolder, current_file: currentFile},
                        function(result){
                            parsed = jQuery.parseJSON(result);
                            if (parsed.result == "error") {
                                var msg = "";
                                $.each(parsed.data, function(i, item) {
                                    msg = msg + "<br />" + item;
                                })
                                reject(msg);
                            }else if(parsed.result == "success"){
                                resolve();
                            }else{
                                reject('An unknown error occured.');
                            }

                        }
                    );
                })
            },
            allowOutsideClick: false
        }).then( function (newFolderName) {
            AppMedia.getFolder(currentFolder);
            $("#media-modal-info").modal("hide");
            swal({
                type: 'success',
                title: 'File was removed successfully! Yay you!',
                html: currentFolder + '/' + currentFile
            })
        }).catch(swal.noop);
    }

    // Get Folder Contents and Display
    this.getFolder = function (folder){

        // Turn on loader empty current files/folders
		$("#loader").show();
		$("#folder_list").html("");
		$("#file_list").html("");

        // Get the current folder contents
        AppCSRF.csrfAjax(
            this.getFolderUrl,
            {directory: folder},
            function(result){
                parsed = jQuery.parseJSON(result);
                $("#breadcrumb").html("");
                $("#folder_list").html("");
                $("#file_list").html("");

                $("#breadcrumb").data("current", folder);

                if (folder == "") {
                    $("#breadcrumb").html('/ &nbsp;<li>uploads</li>');
                }else{
                    $("#breadcrumb").html('/ &nbsp;<li>uploads</li>');
                    var breadcrumbs = folder.split('/');
                    $.each( breadcrumbs, function( key, value ) {
                        if (value != "") {
                            $("#breadcrumb").append('<li>'+ value +'</li>');
                        }

                    });
                }

                if (folder != "") {
                    $("#back-button").show();
                }else{
                    $("#back-button").hide();
                }

                if (parsed.status == "error") {
                    swal(
                        'Oops...',
                        'You do not have permission to access that folder!',
                        'error'
                    )
                }

                $("#loader").hide();
                $.each( parsed.folders, function( key, value ) {
                    $("#folder_list").append('<div class="col-sm-2 col-xs-4 media-folder" data-folder="'+ value +'" style="margin-bottom: 20px;">' +
                        '<div style="line-height: 100px;">' +
                            '<div style="height: 100px; width: 100px; margin: 0 auto 0 auto;"><i class="fa fa-folder" style="font-size: 100px;"></i></div>' +
                        '</div>' +
                        '<div>' +
                            value +
                        '</div>' +
                    '</div>');
                });

                $.each( parsed.files, function( key, value ) {

                    if (value.type == "image") {
                        $("#file_list").append('<div class="col-sm-2 col-xs-4 media-file" data-file="'+ value.file +'" data-filetype="'+ value.type +'" style="margin-bottom: 20px; height: 150px; word-break: break-all;">' +
                            '<div style="line-height: 100px;">' +
                                '<img src="/uploads'+ folder + '/' + value.file +'" style="max-width: 100%; max-height: 100px; border-radius: 5px; margin-bottom: 5px;" />' +
                            '</div>' +
                            '<div>' +
                                value.file +
                            '</div>' +
                        '</div>');
                    }else{
                        $("#file_list").append('<div class="col-sm-2 col-xs-4 media-file" data-file="'+ value.file +'" data-filetype="'+ value.type +'" style="margin-bottom: 20px; height: 150px; word-break: break-all;">' +
                            '<div style="line-height: 100px;">' +
                                '<div style="height: 100px; width: 100px; margin: 0 auto 5px auto;"><i class="fa fa-file" style="font-size: 100px;"></i></div>' +
                            '</div>' +
                            '<div>' +
                                value.file +
                            '</div>' +
                        '</div>');
                    }

                });

            }
        );
    }

    this.getUrlParameter = function(sParam) {

        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }

    };

    this.copyToClipboard = function(text) {
        var id = "mycustom-clipboard-textarea-hidden-id";
        var existsTextarea = document.getElementById(id);

        if(!existsTextarea){
            var textarea = document.createElement("textarea");
            textarea.id = id;
            // Place in top-left corner of screen regardless of scroll position.
            textarea.style.position = 'fixed';
            textarea.style.top = 0;
            textarea.style.left = 0;

            // Ensure it has a small width and height. Setting to 1px / 1em
            // doesn't work as this gives a negative w/h on some browsers.
            textarea.style.width = '1px';
            textarea.style.height = '1px';

            // We don't need padding, reducing the size if it does flash render.
            textarea.style.padding = 0;

            // Clean up any borders.
            textarea.style.border = 'none';
            textarea.style.outline = 'none';
            textarea.style.boxShadow = 'none';

            // Avoid flash of white box if rendered for any reason.
            textarea.style.background = 'transparent';
            document.querySelector("body").appendChild(textarea);
            //console.log("The textarea now exists :)");
            existsTextarea = document.getElementById(id);
        }

        existsTextarea.value = text;
        existsTextarea.select();

    };

    this.refreshInputs = function() {
        $(".dm-input").each(function() {
            var inputName = $(this).data("name");
            var inputValue = $(this).data("value");

            $(this).html(
                '<span class="input-group-btn">'+
                    '<img id="'+inputName+'-thumbnail" src="'+inputValue+'" style="max-height: 30px; max-width: 30px;" />'+
                '</span>'+
                '<input type="text" name="'+inputName+'" id="'+inputName+'" class="form-control" value="'+inputValue+'">'+
                '<span class="input-group-btn">'+
                    '<button type="button" class="btn btn-default image-preview" data-target="'+inputName+'"><i class="fa fa-eye"></i></button>'+
                    '<button type="button" class="btn btn-default image-select" data-target="'+inputName+'"><i class="fa fa-picture-o"></i></button>'+
                '</span>'
            );

        });
    }



    $(document).on('click', '#media-menu', function(){
        AppMedia.loadMedia('menu', null);
    });

    $(document).on('dblclick', '.media-folder', function(){

        var currentFolder = $("#breadcrumb").data('current');
        var newFolder = currentFolder + "/" + $(this).data('folder');
        AppMedia.getFolder(newFolder);

    });

    $(document).on('click', '#back-button', function(){

        var selectedFolder = $("#breadcrumb").data('current');

        var trail = selectedFolder.split('/');
        trail.splice(-1,1);
        newFolder = trail.join('/');

        AppMedia.getFolder(newFolder);

    });

    $(document).on('click', '#refresh-button', function(){

        var currentFolder = $("#breadcrumb").data('current');
        AppMedia.getFolder(currentFolder);

    });

    $(document).on('click', '#new-folder-button', function(){

        var currentFolder = $("#breadcrumb").data('current');
        AppMedia.createFolder(currentFolder);

    });

    $(document).on('dblclick', '.media-file', function(){

        var currentFolder = $("#breadcrumb").data('current');
        var file = $(this).data("file");
        var filetype = $(this).data("filetype");

        var filePath = '/uploads'+currentFolder+'/'+file;

        $("#file-info")
            .data("file", file)
            .data("filepath", filePath)
            .html(file);

        if (filetype == "image") {
            $("#media-image-preview").html('<img src="/uploads/' + currentFolder + '/' + file +'" style="max-width: 100%; max-height: 200px;" />');
        }

        $("#file-name").html(file);
        $("#file-path").html(filePath);

        $("#media-clipboard").val(filePath);
        $("#media-download-file").attr("href", filePath);


        $("#media-modal-info").modal("show");

        new Clipboard('#media-info-btn-copy');


    });

    $(document).on('hidden.bs.modal', '#media-modal-info', function(){
        $("#media-image-preview").html("");
        $("#file-name").html("");
        $("#file-path").html("");
        $("#file-info").data("file", "").html("");
        $("#media-clipboard").val("");
        $("#media-download-file").attr("href", "");
    });

    $(document).on('click', '#media-info-btn-copy', function(){

        var filePath = $("#file-info").data("filepath");

        AppMedia.copyToClipboard(filePath);
        swal({
            title: '<span style="color: green;">Image url copied to clipboard!</span>',
            text: filePath
        }).catch(swal.noop);

    });

    $(document).on('click', '#media-info-btn-download', function(){

        document.getElementById('media-download-file').click();

    });

    $(document).on('click', '#media-info-btn-delete', function(){

        var currentFolder = $("#breadcrumb").data("current")
        var currentFile = $("#file-info").data("file");

        AppMedia.deleteMedia(currentFolder, currentFile);


    });


    $(document).on('click', '.image-preview', function(e){
        e.preventDefault();
        var target = $(this).attr('data-target');
        swal({
            html: '<img src="'+ $("#"+target).val() +'" style="width: 100%;">'
        }).catch(swal.noop);

    });

    $(document).on('click', '#upload-button', function(){
        var currentFolder = $("#breadcrumb").data("current");
        $("#current_folder").val(currentFolder);
        $('#fileupload').trigger('click');

        $('#fileupload').fileupload({
            autoUpload : true,
            singleFileUploads: false,
            add: function (e, data) {
                $.getJSON('/csrf', function(csrfData){
                    processCsrf(csrfData);
                });

                function processCsrf(csrfData){
                    data.formData = {};
                    data.formData[csrfData.name_key] = csrfData.name;
                    data.formData[csrfData.value_key] = csrfData.value;
                    data.submit();
                }
            },
            done: function (e, data) {
                AppMedia.getFolder(currentFolder);
            }
        });

    });

    $(document).ready(function() {

        $(".dm-input").each(function() {
            var inputName = $(this).data("name");
            var inputValue = $(this).data("value");

            $(this).html(
                '<span class="input-group-btn">'+
                    '<img id="'+inputName+'-thumbnail" src="'+inputValue+'" style="max-height: 30px; max-width: 30px;" />'+
                '</span>'+
                '<input type="text" name="'+inputName+'" id="'+inputName+'" class="form-control" value="'+inputValue+'">'+
                '<span class="input-group-btn">'+
                    '<button type="button" class="btn btn-default image-preview" data-target="'+inputName+'"><i class="fa fa-eye"></i></button>'+
                    '<button type="button" class="btn btn-default image-select" data-target="'+inputName+'"><i class="fa fa-picture-o"></i></button>'+
                '</span>'
            );

        });

        var clExists;
        try { AppCloudinary; clExists = true;} catch(e) {}
        if (clExists) {
            $(document).on('click', ".image-select", function(e){
                e.preventDefault();
                AppCloudinary.loadCloudinary("input", $(this).attr('data-target'));
            });
        }else{
            $(document).on('click', ".image-select", function(e){
                e.preventDefault();
                AppMedia.loadMedia("input", $(this).attr('data-target'));
            });
        }
    });

    $(document).on('click', '#media-info-btn-insert', function(){

        var filePath = $("#file-info").data("filepath");

        var target = AppMedia.getUrlParameter('target');
        var source = AppMedia.getUrlParameter('source');

        if (source == "input") {
            $(parent.document).find("#"+target).val(filePath);
            $(parent.document).find("#"+target+"-thumbnail").attr("src", filePath);
            window.parent.$("#media-modal").modal("hide");
        }else if (source == "blog") {
            $.getScript( "https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.7.1/tinymce.min.js", function( data, textStatus, jqxhr ) {
                top.tinymce.activeEditor.insertContent('<img src="'+filePath+'" class="img-responsive" style="width: 100%;" />');
                window.parent.$("#media-modal").modal("hide");
            });

        }else if (source == "file_uploaded") {
            window.parent.$("#featured_thumbnail").html('<script>PDFObject.embed("'+filePath+'", "#viewfile");</script>');
            window.parent.$("#featured_image").val(filePath);
            window.parent.$("#media-modal").modal("hide");
        }else if (source == "blog_featured") {
            window.parent.$("#featured_thumbnail").html('<img src="'+filePath+'" class="img-responsive" alt="Featured Image" style="width: 100%;">');
            window.parent.$("#featured_image").val(filePath);
            window.parent.$("#media-modal").modal("hide");
        }else if (source == "seo_featured") {
            window.parent.$("#featured_thumbnail").html('<img src="'+filePath+'" class="img-responsive" alt="Featured Image" style="width: 100%;">');
            window.parent.$("#featured_image").val(filePath);
            window.parent.$("#media-modal").modal("hide");
        }

    });
};
