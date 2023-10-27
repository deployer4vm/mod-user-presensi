<template>
    <b-card-body class="pb-2">
        <h5>{{ Trans.get('user_config.form_auth.label.otp') }}</h5>
        <hr class="border-light">

        <!-- otp enabled -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.otp.otp_enabled')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.auth.otp.form.otp_enabled"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.auth.otp.enable"
            />
        </b-form-group>

        <!-- otp digit -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.otp.otp_digit')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-input 
                type="number"
                v-model.trim="data.auth.otp.form.otp_digit" 
                :placeholder="Trans.get('user_config.form_auth.input_description.otp.otp_digit')"
                :disabled="!AppConfig.packageLocal.moduser.auth.otp.enable"
            />
        </b-form-group>

        <!-- otp timeout -->
        <b-form-group
            :label="Trans.get('user_config.form_auth.input_caption.otp.otp_timeout')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-input-group>
                <b-input 
                    type="number"
                    v-model.trim="data.auth.otp.form.otp_timeout" 
                    :placeholder="Trans.get('user_config.form_auth.input_description.otp.otp_timeout')"
                    :disabled="!AppConfig.packageLocal.moduser.auth.otp.enable"
                    :state="$v.data.auth.otp.form.otp_timeout.$error ? false : null"
                    @change="$v.data.auth.otp.form.otp_timeout.$touch()"
                />

                <b-input-group-text slot="append">{{ Trans.get('user_config.form_auth.label.minute') }}</b-input-group-text>
            </b-input-group>

            <invalid-tooltip
                :inputItem="$v.data.auth.otp.form.otp_timeout"
                :fieldName="Trans.get('user_config.form_auth.input_caption.otp.otp_timeout')"
            />
        </b-form-group>
        
        <!-- otp channel * -->
        <hr class="border-light">
        <h5>{{ Trans.get('user_config.form_auth.input_caption.otp.otp_channel') }}</h5>
        <template v-for="(v, k) in AppConfig.packageLocal.moduser.auth.otp.enable_channel">
            <b-form-group
                :label="Trans.get('user_config.form_auth.input_caption.otp.otp_channel_'+k)"
                label-align-md="right"
                label-class="pr-md-2"
                :label-cols-md="3"
            >
                <b-radio-group
                    v-model.trim="data.auth.otp.form['otp_channel_'+k]"
                    :options="[
                        { text: Trans.get('lang.disable'), value: 0 },
                        { text: Trans.get('lang.enable'), value: 1 }
                    ]"
                    :disabled="!AppConfig.packageLocal.moduser.auth.otp.enable || !v"
                />
            </b-form-group>
        </template>

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
import { maxValue } from "node_modules/vuelidate/lib/validators";

export default {
    name: "moduser-config-auth-otp",

    validations: {
        data: {
            auth: {
                otp: {
                    form: {
                        otp_timeout: {
                            maxValue: maxValue(10)
                        }
                    }
                }
            }
        }
    },

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
                    this.data.auth.otp.form[v.key] = parseInt(v.value)
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

            if (!this.UserAuth.hasAccess(this.data.auth.otp.accessRuleKey,'has_access',false)) {
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

            if (this.data.auth.otp.form.otp_digit < 1)
                this.data.auth.otp.form.otp_digit = 8;

            if (this.data.auth.otp.form.otp_timeout < 1)
                this.data.auth.otp.form.otp_timeout = 5;

            _.forEach(this.data.auth.otp.form,(val, k, i)=>{
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
        if (!this.UserAuth.hasAccess(this.data.auth.otp.accessRuleKey)) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning",
            });
            return false;
        }

        if (this.data.auth.otp.loaded == false) {
            this.loadData();
            this.data.auth.otp.loaded = true;
        }   
    },
}
</script>