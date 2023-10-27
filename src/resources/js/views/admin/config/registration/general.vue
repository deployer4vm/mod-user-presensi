<template>
    <b-card-body class="pb-2">
        <h5>{{ Trans.get('user_config.form_registration.label.general') }}</h5>
        <hr class="border-light">

        <!-- enable -->
        <b-form-group
            :label="Trans.get('user_config.form_registration.input_caption.general.enable')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.registration.general.form.enable"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.registration.enable"
            />
        </b-form-group>

        <!-- auto active -->
        <b-form-group
            :label="Trans.get('user_config.form_registration.input_caption.general.auto_active')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.registration.general.form.auto_active"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.registration.auto_active"
            />
        </b-form-group>

        <!-- admin send activation email -->
        <b-form-group
            :label="Trans.get('user_config.form_registration.input_caption.general.admin_send_activation_email')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.registration.general.form.admin_send_activation_email"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.registration.admin_send_activation_email"
            />
        </b-form-group>

        <!-- tos confirm -->
        <b-form-group
            :label="Trans.get('user_config.form_registration.input_caption.general.tos_confirm')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <b-radio-group
                v-model.trim="data.registration.general.form.tos_confirm"
                :options="[
                    { text: Trans.get('lang.disable'), value: 0 },
                    { text: Trans.get('lang.enable'), value: 1 }
                ]"
                :disabled="!AppConfig.packageLocal.moduser.registration.tos_confirm"
            />
        </b-form-group>

        <!-- default role code -->
        <b-form-group
            :label="Trans.get('user_config.form_registration.input_caption.general.default_role_code')"
            label-align-md="right"
            label-class="pr-md-2"
            :label-cols-md="3"
        >
            <v-single-select
                :modelData="data.registration.general.form.default_role_code"
                @onSelect="data.registration.general.form.default_role_code = $event"
                :options="selectRoles"
                :placeholder="Trans.get('lang.select')"
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
    name: "moduser-config-registration-general",

    data: () => ({
        selectRoles: [],
    }),

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
                    if (v.key == 'default_role_code') {
                        this.data.registration.general.form[v.key] = v.value
                    } else {
                        this.data.registration.general.form[v.key] = parseInt(v.value)
                    }
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

            _.forEach(this.data.registration.general.form,(val, k, i)=>{
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
        //
        loadListRoles() {
            this.$store.dispatch("role/roleList", {
                }).then(res => {
                    _.forEach(res.data,(v,k)=>{
                        if (v.level != 0 || (this.UserAuth.getUser('level') != 0 && v.level < this.UserAuth.getUser('level'))) {
                            this.selectRoles.push({
                                text: '[' + v.role_code + '] - ' + v.name,
                                value: v.role_code,
                            });
                        }
                    });
                    this.Web.setLoadingPage(false);
                }).catch((res)=>{
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text: this.Trans.get("alert.read_failed",{attribute:''}) + "<br>\n" + res.message,
                        type: "warning"
                    });
                    this.Web.setLoadingPage(false);
                });
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

        if (this.data.registration.general.loaded == false) {
            this.loadData();
            this.loadListRoles();
            this.data.registration.general.loaded = true;
        }   
    },
}
</script>