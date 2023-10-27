<template>
    <div>
        <header-breadcrumb :pageTitle="pageTitle" :showBack="false" />

        <b-container fluid>
            <b-card no-body class="my-3">
                <div class="row no-gutters row-bordered row-border-light overflow-visible">
                    <div class="col-md-3 pt-0">
                        <b-list-group class="account-settings-links" flush>
                            <b-list-group-item 
                                button 
                                :active="viewData.registration.curTab === 'general'"
                                @click="viewData.registration.curTab = 'general'"
                            >
                                {{Trans.get('user_config.form_registration.label.general')}}
                            </b-list-group-item>

                            <b-list-group-item 
                                button 
                                :active="viewData.registration.curTab === 'tos'"
                                @click="viewData.registration.curTab = 'tos'"
                            >
                                {{Trans.get('user_config.form_registration.label.tos')}}
                            </b-list-group-item>
                        </b-list-group>
                    </div>

                    <div class="col-md-9" v-if="viewData.registration.curTab === 'general'">
                        <general></general>
                    </div>
                    <div class="col-md-9" v-if="viewData.registration.curTab === 'tos'">
                        <tos></tos>
                    </div>
                </div>
            </b-card>
        </b-container>
    </div>
</template>

<script>
import general from './general';
import tos from './tos';

export default {
    name: "moduser-config-registration",

    components: {
        general,
        tos
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
            return this.Trans.chose(this.Trans.get('user_config.form_registration.name'));
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
        if (!this.UserAuth.hasAccess(this.viewData.registration.accessRuleKey)) {
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