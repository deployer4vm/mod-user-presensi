<template>
    <b-card-body class="pb-2">
        <h5>{{ Trans.get('user_config.form_auth.label.pin') }}</h5>
        <hr class="border-light">

        <!-- pin enabled -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.pin.pin')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.auth.pin.form.pin"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.auth.pin.enable"
            />
        </b-form-group>

        <!-- pin digit -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.pin.pin_digit')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-input 
                type="number"
                v-model.trim="data.auth.pin.form.pin_digit" 
                :placeholder="Trans.get('user_config.form_auth.input_description.pin.pin_digit')"
                :disabled="!AppConfig.packageLocal.moduser.auth.pin.enable"
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
    name: "moduser-config-auth-pin",

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
                    this.data.auth.pin.form[v.key] = parseInt(v.value)
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

            if (!this.UserAuth.hasAccess(this.data.auth.pin.accessRuleKey,'has_access',false)) {
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

            if (this.data.auth.pin.form.pin_digit < 1)
                this.data.auth.pin.form.pin_digit = 5;

            _.forEach(this.data.auth.pin.form,(val, k, i)=>{
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
        if (!this.UserAuth.hasAccess(this.data.auth.pin.accessRuleKey)) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning",
            });
            return false;
        }

        if (this.data.auth.pin.loaded == false) {
            this.loadData();
            this.data.auth.pin.loaded = true;
        }   
    },
}
</script>