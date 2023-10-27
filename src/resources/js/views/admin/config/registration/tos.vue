<template>
    <b-card-body class="pb-2">
        <h5>{{ Trans.get('user_config.form_registration.label.tos') }}</h5>
        <hr class="border-light">

        <!-- tos content -->
        <b-form-group
            :label="Trans.get('user_config.form_registration.input_caption.tos.tos_content')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <textarea
                style="width: 100%; min-height: 250px;"
                :value="data.registration.tos.form.tos_content"
                rows="12"
                name="tos_content"
                id="editor-content"
            />
        </b-form-group>

        <div class="text-center">
            <hr class="border-light">
            <b-btn
                variant="success w-icon btn-sm"
                @click="formSubmitted"
            >
               <i class="fi fi-rs-disk"></i> 
               <span>{{ Trans.get('lang.save_change') }}</span>
            </b-btn>
        </div>
    </b-card-body>
</template>

<script>
export default {
    name: "moduser-config-registration-tos",

    data: () => ({}),

    computed: {
        data: {
            get() {
                return this.$store.state.moduserView.dataConfigSetting;
            },
            set(value) {
                this.$store.commit("moduserView/setDataConfigSetting", value);
            }
        },
    },

    methods: {        
        loadData() {
            this.Web.setLoadingPage(true);
            this.$store.dispatch("reloadConfig",{
                'group[0]': 'moduser_registration',
                saveState: false
            })
            .then((res) => {
                res.forEach((v,k) => {
                    this.data.registration.tos.form[v.key] = JSON.parse(JSON.stringify(v.value));
                });
                this.Web.setLoadingPage(false);
            })
            .catch((res) => {
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.read_failed", { attribute: 'Config' }) + "<br>\n" + res.message,
                    type: "warning",
                });
                this.Web.setLoadingPage(false);
            });
        },
        formSubmitted(ev) {
            ev.preventDefault();

            if (this.$v) {
                this.$v.$touch();
                if (this.$v.$error) {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.form_must_complete_title"),
                        text: this.Trans.get("alert.form_must_complete_text"),
                        type: "warning",
                    });
                    return false;
                }
            }

            if (!this.UserAuth.hasAccess(this.data.registration.accessRuleKey,'has_access',false)) {
                //goto dashboard current tenant
                this.Web.goToCurrentTenant();
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.access_denied"),
                    type: "warning",
                });
                return false;
            }

            var data = [];

            this.data.registration.tos.form.tos_content = $('#editor-content').val();
            var formData = JSON.parse(JSON.stringify(this.data.registration.tos.form));
            formData.tos_content = $('#editor-content').val();

            _.forEach(formData,(val, k, i)=>{
                data.push({
                    'group':'moduser_registration',
                    'key':k,
                    'value': val
                });
            });

            this.Web.setLoadingPage(true);

            this.$store
            .dispatch("saveConfig", {data: data,saveState: false})
            .then((res) => {
                this.Web.showAlert({
                    title: this.Trans.get("alert.success_title"),
                    text: this.Trans.get("alert.update_success", { attribute: 'Config' }),
                    type: "success",
                });
                this.Web.setLoadingPage(false);
            })
            .catch((res) => {
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.update_failed", { attribute: 'Config' }) + "<br>\n" + res.message,
                    type: "warning",
                });
                this.Web.setLoadingPage(false);
            });
        },
        initEditor(){
            var that = this;
            setTimeout(function(){
                that.editorContent = KindEditor.create('#editor-content', {
                    allowFileManager: false,
                    themeType: "simple",
                    afterCreate:function() {
                    },
                    afterBlur:function() {
                        this.sync();
                    }
                });
            }, 500);
        },
    },

    created() {
        if (!this.UserAuth.hasAccess(this.data.registration.accessRuleKey)) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning",
            });
            return false;
        }

        if (this.data.registration.tos.loaded == false) {
            this.loadData();
            this.data.registration.tos.loaded = true;
        }   
        
        this.initEditor();
    },
}
</script>