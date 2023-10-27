<template>
    <div>
        <header-breadcrumb :pageTitle="pageTitle" :showBack="false" />

        <b-container fluid>
            <b-card no-body class="my-3">
                <div class="row no-gutters row-bordered row-border-light overflow-visible">
                    <div class="col-md-3 pt-0">
                        <b-list-group class="account-settings-links" flush>
                            <b-list-group-item 
                                v-if="viewData.auth.password.accessRuleKey"
                                button 
                                :active="viewData.auth.curTab === 'password'"
                                @click="viewData.auth.curTab = 'password'"
                            >
                                {{Trans.get('user_config.form_auth.label.password')}}
                            </b-list-group-item>

                            <b-list-group-item 
                                v-if="viewData.auth.otp.accessRuleKey"
                                button 
                                :active="viewData.auth.curTab === 'otp'"
                                @click="viewData.auth.curTab = 'otp'"
                            >
                                {{Trans.get('user_config.form_auth.label.otp')}}
                            </b-list-group-item>

                            <b-list-group-item 
                                v-if="viewData.auth.pin.accessRuleKey"
                                button 
                                :active="viewData.auth.curTab === 'pin'"
                                @click="viewData.auth.curTab = 'pin'"
                            >
                                {{Trans.get('user_config.form_auth.label.pin')}}
                            </b-list-group-item>

                            <b-list-group-item 
                                v-if="viewData.auth.login.accessRuleKey"
                                button 
                                :active="viewData.auth.curTab === 'login'"
                                @click="viewData.auth.curTab = 'login'"
                            >
                                {{Trans.get('user_config.form_auth.label.login')}}
                            </b-list-group-item>
                        </b-list-group>
                    </div>

                    <div class="col-md-9" v-if="viewData.auth.password.accessRuleKey && viewData.auth.curTab === 'password'">
                        <password></password>
                    </div>
                    <div class="col-md-9" v-if="viewData.auth.otp.accessRuleKey && viewData.auth.curTab === 'otp'">
                        <otp></otp>
                    </div>
                    <div class="col-md-9" v-if="viewData.auth.pin.accessRuleKey && viewData.auth.curTab === 'pin'">
                        <pin></pin>
                    </div>
                    <div class="col-md-9" v-if="viewData.auth.login.accessRuleKey && viewData.auth.curTab === 'login'">
                        <login></login>
                    </div>
                </div>
            </b-card>
        </b-container>
    </div>
</template>

<script>
import password from './password';
import otp from './otp';
import pin from './pin';
import login from './login';

export default {
    name: "moduser-config-auth",

    components: {
        password,
        otp,
        pin,
        login
    },

    data: () => ({}),

    watch: {},

    computed: {
        viewData: {
            get() {
                return this.$store.state.moduserView.dataConfigSetting;
            },
            set(value) {
                this.$store.commit("moduserView/setDataConfigSetting", value);
            }
        },
        pageTitle() {
            return this.Trans.chose(this.Trans.get('user_config.form_auth.name'));
        },
    },

    methods: {
        initView() {
            this.Web.setModule("moduser");

            this.Web.setNavbarTitle(this.pageTitle);

            this.Web.resetBreadcrumb();
            this.Web.addBreadcrumb(this.Trans.get("lang.home"));
            this.Web.addBreadcrumb(this.Trans.get("user.name"));
            this.Web.addBreadcrumb(this.Trans.get("user_config.name"));
            this.Web.addBreadcrumb(this.pageTitle);

            this.Web.setBodyWithPadding(false);
            this.Web.setShow("moduser");
        },
    },

    created() {
        if (!this.UserAuth.hasAccess(this.viewData.auth.accessRuleKey)) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning",
            });
            return false;
        }

        this.initView();
    },
}
</script>