<div class="row">
    <div class="col-md-4">
        <div class="card card-primary status-card">
            <div class="card-header" style="background-color: #007bff; color: #fff;">
                <h3 class="card-title">{{ __('PHP Information')}}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12"><strong>{{ __('Supported PHP Versions')}}</strong></div>
                        <div class="col-12" id="supportedPhps">
                            <div><span class="spinner-border spinner-border-sm"></span></div>
                            <p></p>
                        </div>
                </div>
                <div class="row">
                    <div class="col-12"><strong>{{ __('Installed PHP Versions')}}</strong></div>
                        <div class="col-12" id="installedPhps">
                            <div><span class="spinner-border spinner-border-sm"></span></div>
                            <p></p>
                        </div>
                </div>
                <div class="card card-primary card-outline" id="installed_modules_area">
                    <div class="card-body box-profile">
                        <h3 class="profile-username text-center">{{ __('Installed PHP Modules')}}</h3>
                        <div style="height:200px; overflow-y: auto;"> 
                            <pre id="installedModules"></pre>
                        </div>
                    </div>
                    <div class="overlay">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">{{ __('Loading') }}...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <div style="margin-bottom: 1em">
                    <button type="button" class="btn btn btn-success" onclick="installModules_Selections()"><i class="fas fa-download mr-1"></i>{{ __('Install Selected Modules')}}</button>
                    <button type="button" class="btn btn btn-success" onclick="installModules_Manual()"><i class="fas fa-download mr-1"></i>{{ __('Install Module')}}</button>
                </div>
                <div id = "phpModule-table" class="table-content">
                    <div class="table-body"></div>
                    <div class="overlay">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">{{ __('Loading') }}...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    function getModulesContent(){
        getModules();
        getInstalledPhps();
        getInstalledModules();
        getSupportedPhps();
    }

    function getModules() {
        request("{{API('get_php_modules')}}", new FormData(), function(response) {
            $('#phpModule-table').find('.table-body').html(response).find("table").DataTable(dataTablePresets('normal'));
            $('#phpModule-table').find('.overlay').hide();
        }, function(response) {
            const error = JSON.parse(response).message;
            showSwal(error, 'error', 2000);
        });
    }
    
    function getSupportedPhps(){
        request("{{API('get_supported_phps')}}", new FormData(), function(response){
            const output = JSON.parse(response).message;
            $('#supportedPhps').find("div").html("");
            $('#supportedPhps').find("p").html(output.join(" - "));
        }, function(response){
            const error = JSON.parse(response).message;
            showSwal(error, 'error', 2000);
        });
    }
    
    function getInstalledPhps(){
        request("{{API('get_installed_phps')}}", new FormData(), function(response){
            const output = JSON.parse(response).message;
            $('#installedPhps').find("div").html("");
            $('#installedPhps').find("p").html(output.join(" - "));
        }, function(response){
            const error = JSON.parse(response).message;
            showSwal(error, 'error', 2000);
        });
    }

    function getInstalledModules(){
        $('#installed_modules_area').find('.overlay').show();
        request("{{API('get_installed_modules')}}", new FormData(), function(response){
            const output = JSON.parse(response).message;
            $('#installedModules').html(output.join(" | "));
            $('#installed_modules_area').find('.overlay').hide();
        }, function(response){
            const error = JSON.parse(response).message;
            showSwal(error, 'error', 2000);
        });
    }

    function checkAllModules(){
        $('#phpModule-table').find('input[type="checkbox"]').each(function() {
            this.checked = $('#phpModule-table').find('#btSelectAll').prop('checked');
        });
    }

    function installModules_Selections(){
        var modules = [];

        $('#phpModule-table').find('input[name$="btSelectItem"]').each(function () {
            if(this.checked){
                modules.push(this.value);
            }
        });
        if(modules.length === 0){
            showSwal("{{__('Please make a selection first!')}}", 'error', 2000);
            return false;
        }

        installModule(modules);
    }

    function installModules_Manual(){
        Swal.fire({
              title: "{{__('Module Name')}}",
              input: 'text',
              inputPlaceholder: "{{__('Enter the module name')}} (e.g. common)",
              showCancelButton: true,
              confirmButtonColor: "#28a745",
              confirmButtonText: "{{__('Install')}}", cancelButtonText: "{{__('Cancel')}}",
              inputValidator: (value) => {
                if (!value) {
                    return "{{__('Please enter a valid module name')}}!";
                }
              }
            }).then((result) => {
              if (result.value) {
                installModule(result.value);
              }
            });
    } 

    function installModule(modules){
        showSwal('{{__("Initializing")}}...','info'); //Başlatılıyor
        let data = new FormData();
        data.append('moduleList', modules);
        request(API('install_module'), data, function (response) {
            const output = JSON.parse(response).message;
            Swal.close();
            $('#installModuleModal').modal({backdrop: 'static', keyboard: false})
            $('#installModuleModal').find('.modal-body').html(output);
            $('#installModuleModal').modal("show"); 
        }, function(response){
            const error = JSON.parse(response).message;
            showSwal(error,'error',2000);
        });
    }
    

    $('#installModuleModal').on('hidden.bs.modal', function () {
        $(this).find('.modal-body').html("");
    })

    function onTaskSuccess(){
        showSwal('{{__("Your request has been successfully completed")}}', 'success', 2000);
        setTimeout(function(){
            $('#installModuleModal').modal("hide"); 
        }, 2000);
        reload();
    }

    function onTaskFail(){
        showSwal('{{__("An error occurred while processing your request")}}!', 'error', 2000);
    }
    
</script>