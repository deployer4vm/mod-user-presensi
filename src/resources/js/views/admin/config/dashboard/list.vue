<template>
    <div>
        <header-breadcrumb
            :pageTitle="pageTitle"
            :showBack="false"
        />

        <b-container fluid>
            <b-card class="my-3" no-body>
                <b-card-body>
                    <div class="d-flex flex-column flex-md-row justify-content-end">
                        <div class="d-flex flex-wrap flex-md-nowrap align-items-center">
                             <router-link 
                                v-if="UserAuth.hasAccess(accessRuleKey, 'r')" 
                                class="btn btn-sm btn-primary w-icon w-50 w-md-auto"
                                :to="{ name: 'moduser.config.dashboard.add' }"
                            >
                                <i class="fi fi-rs-add"></i>
                                <span>{{ Trans.get("user_config.form_dashboard.label.add_config") }}</span>
                            </router-link>
                        </div>
                    </div>

                </b-card-body>
            </b-card>

            <b-card class="my-3" no-body>
                <!-- Header -->
                <b-card-header>
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="my-1">{{ Trans.get('coop_master.form_config_dashboard.name') }}</h5>
                    </div>
                </b-card-header>

                <!-- Table list data -->
                <div class="table-responsive mb-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.name') }}</th>
                                <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.description') }}</th>
                                <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.tenant_id') }}</th>
                                <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.template_code') }}</th>
                                <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.feature') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(v, i) in listData.data" :key="'dashboard.listdata-' + i">
                                <td>
                                    {{ v.name }}
                                </td>
                                <td>
                                    {{ v.description }}
                                </td>
                                <td>
                                    {{ v.tenant }}
                                </td>
                                <td>
                                    {{ v.template_code }}
                                </td>
                                <td>
                                    <template v-if="v.feature">
                                        <ul v-for="(value, key) in v.feature" :key="'dashboard.listdata-' + i + '-' + key">
                                            <li>
                                                {{ value }}
                                            </li>
                                        </ul>
                                    </template>                                    
                                </td>
                                <td style="width: 80px;" class="text-center">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <router-link
                                            v-if="UserAuth.hasAccess(accessRuleKey, 'r')"
                                            class="btn btn-sm btn-dark icon-btn"
                                            :to="{ name: 'moduser.config.dashboard.edit', params: { id: v.id } }"
                                        >
                                            <i class="fi fi-rs-edit"></i>
                                        </router-link>

                                        <b-btn
                                            @click="deleteData(v.id)"
                                            variant="danger btn-sm icon-btn"
                                            v-b-tooltip.hover.left
                                            :title="Trans.get('lang.delete')"
                                            v-if="UserAuth.hasAccess(accessRuleKey, 'has_access')"
                                        >
                                            <i class="fi fi-rs-trash"></i>
                                        </b-btn>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="listData.data.length == 0">
                                <td colspan="6" class="text-center">{{ noResultsText }}</td>
                            </tr>
                        </tbody>
                    </table>
                 </div>

            </b-card>
        </b-container>

    </div>
</template>

<script>

export default {
    name: "master-config-dashboard",
    data: () => ({
        accessRuleKey: "modcoop.master.config.dashboard",
        noResultsText: "Tidak Ada Data",
        // 
        // listData: []
    }),
    watch: {},
    computed: {
        pageTitle() {
            return this.Trans.chose(
                this.AppConfig.packageLocal.modcoop.access.children.master.children.config.children.dashboard.caption
            );
        },
        listData() {
            return this.$store.state.userConfig.dashboardList;
        },
        // apakah tenant manager
        isOnTM(){
            return isOnTenantManager;
        },
    },
    methods: {
        loadList() {
            this.Web.setLoadingPage(true);
            this.$store.dispatch("userConfig/listDashboard")
                .then((res) => {
                    if (this.listData.data.length == 0) {
                        this.noResultsText = this.Trans.get("lang.no_data");
                        this.Web.showAlert({
                            title: this.Trans.get("alert.info_title"),
                            text: this.Trans.get("lang.no_data"),
                            type: "info",
                        });
                    }
                    this.Web.setLoadingPage(false);
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.read_failed", {
                                attribute: "",
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                    this.Web.setLoadingPage(false);
                });
        },
        deleteData(id) {
            if (!this.UserAuth.hasAccess(this.accessRuleKey, "has_access")) {
                //goto dashboard current tenant
                this.Web.goToCurrentTenant();
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.access_denied"),
                    type: "warning",
                });
                return false;
            }

            this.Web.showAlert({
                styleType: "modal",
                type: "warning",
                title: this.Trans.get("alert.delete_confirm_title"),
                text: this.Trans.get("alert.delete_confirm_text"),
                modalButtonCancel: this.Trans.get("lang.no"),
                modalButtonOk: this.Trans.get("lang.yes"),
                onOk: () => {
                    this.$store
                        .dispatch("userConfig/deleteDashboard", { id: id })
                        .then((res) => {
                            this.Web.showAlert({
                                title: this.Trans.get("alert.success_title"),
                                text: this.Trans.get("alert.delete_success", {
                                    attribute: "",
                                }),
                                type: "success",
                            });
                            this.loadList();
                        })
                        .catch((res) => {
                            this.Web.showAlert({
                                title: this.Trans.get("alert.warning_title"),
                                text:
                                    this.Trans.get("alert.delete_failed", {
                                        attribute: "",
                                    }) +
                                    "<br>\n" +
                                    res.message,
                                type: "warning",
                            });
                        });
                },
            });
        },
        initView() {
            this.Web.setModule("modcoop");
                
                this.Web.setNavbarTitle(
                    this.Trans.chose(this.AppConfig.packageLocal.modcoop.access.caption)
                );

                this.Web.resetBreadcrumb();
                this.Web.addBreadcrumb(this.Trans.get('lang.home'), {name: 'home'});
                this.Web.addBreadcrumb(
                    this.Trans.chose(this.AppConfig.packageLocal.modcoop.access.caption)
                );
                this.Web.addBreadcrumb(
                    this.Trans.chose(this.AppConfig.packageLocal.modcoop.access.children.master.caption),
                    {name: 'modcoop.Master'}
                );
                this.Web.addBreadcrumb(
                    this.Trans.chose(this.AppConfig.packageLocal.modcoop.access.children.master.children.config.caption)
                );
                this.Web.addBreadcrumb(
                    this.Trans.chose(this.AppConfig.packageLocal.modcoop.access.children.master.children.config.children.dashboard.caption)
                );

                this.Web.setBodyWithPadding(false);
                this.Web.setShow("modcoop");
        }
    },
    created() {
        this.initView();
        this.loadList();
    },
};
</script>
