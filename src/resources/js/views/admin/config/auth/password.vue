<template>
    <b-card-body class="pb-2">
        <h5>{{ Trans.get('user_config.form_auth.label.password') }}</h5>
        <hr class="border-light">

        <!-- password minlength -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.password.password_minlength')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-input 
                type="number"
                v-model.trim="data.auth.password.form.password_minlength" 
                :placeholder="Trans.get('user_config.form_auth.input_description.password.password_minlength')"
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
    name: "moduser-config-auth-password",

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
                    this.data.auth.password.form[v.key] = parseInt(v.value)
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

            if (!this.UserAuth.hasAccess(this.data.auth.password.accessRuleKey,'has_access',false)) {
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

            this.data.auth.password.form.password_minlength = parseInt(this.data.auth.password.form.password_minlength);

            if (this.data.auth.password.form.password_minlength < 1)
                this.data.auth.password.form.password_minlength = 8;

            _.forEach(this.data.auth.password.form,(val, k, i)=>{
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
        if (!this.UserAuth.hasAccess(this.data.auth.password.accessRuleKey)) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning",
            });
            return false;
        }

        if (this.data.auth.password.loaded == false) {
            this.loadData();
            this.data.auth.password.loaded = true;
        }   
    },
}
</script>