var DropzoneDemo = {
    init: function () {

        Dropzone.options.mDropzoneTwo = {
            paramName: "file",
            maxFilesize: 2,
            addRemoveLinks: true,
            acceptedFiles: "application/xml,text/xml,.xml",
            dictFileTooBig: "Arquivo é muito grande ({{filesizeMB}}), {{maxFilesize}}MB permitido",
            dictCancelUpload: "Cancelar envio",
            dictRemoveFile: "Remover da lista",
            init: function () {
                let hasError = false;
                this.on('sending', function (file, xhr, formData) {
                    formData.append("type", $('#xml_type').val());
                    formData.append("brand", $('#xml_brand').val());
                });
                this.on('success', function () {
                    hasError = false;
                });
                this.on('error', function () {
                    hasError = true;
                });
                this.on('queuecomplete', function () {
                    if (hasError === false) {
                        location.reload();
                    }
                });
            }
        };
    }
};
DropzoneDemo.init();