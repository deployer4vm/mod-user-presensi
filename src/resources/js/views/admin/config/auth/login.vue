<template>
    <b-card-body class="pb-2">
        <h5>{{ Trans.get('user_config.form_auth.label.login') }}</h5>
        <hr class="border-light">

        <!-- rememberme enabled -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.login.login_rememberme')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.auth.login.form.login_rememberme"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.auth.login.rememberme"
            />
        </b-form-group>

        <!-- forgotpassword enabled -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.login.login_forgotpassword')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.auth.login.form.login_forgotpassword"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.auth.login.forgotpassword"
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
    name: "moduser-config-auth-login",

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
                'group[0]': 'moduser_auth',
                saveState: false
            })
            .then((res) => {
                res.forEach((v,k) => {
                    this.data.auth.login.form[v.key] = parseInt(v.value)
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

            if (!this.UserAuth.hasAccess(this.data.auth.login.accessRuleKey,'has_access',false)) {
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

            _.forEach(this.data.auth.login.form,(val, k, i)=>{
                data.push({
                    'group':'moduser_auth',
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
        }
    },

    created() {
        if (!this.UserAuth.hasAccess(this.data.auth.login.accessRuleKey)) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning",
            });
            return false;
        }

        if (this.data.auth.login.loaded == false) {
            this.loadData();
            this.data.auth.login.loaded = true;
        }   
    },
}
</script>