<template>
    <div>
        <!-- Filters -->
        <!-- <div class="ui-bordered px-4 pt-4 mb-4">
      <div class="form-row align-items-center">
        <div class="col-md mb-4">
          <label class="form-label">Role</label>
          <b-select v-model="filterRole" :options="['Any', 'SPV', 'Manager Bagian', 'Manager Utama', 'Enjin']" />
        </div>
        <div class="col-md mb-4">
          <label class="form-label">Status</label>
          <b-select v-model="filterStatus" :options="['Any', 'Active', 'Banned', 'Deleted']" />
        </div>
        <div class="col-md col-xl-2 mb-4">
          <label class="form-label d-none d-md-block">&nbsp;</label>
          <b-btn variant="secondary" :block="true">Show</b-btn>
        </div>
      </div>
    </div> -->
        <!-- / Filters -->
        <header-breadcrumb :pageTitle="pageTitle" :showBack="false" />

        <b-card class="m-3" no-body>
            <b-card-body>
                <div class="d-flex form-row flex-xl-row align-items-end">
                    <b-form-group :label="Trans.get('pagination.per_page')" class="d-inline-block col-md mt-1">
                        <b-select v-model="perPage" :options="[10, 20, 30, 40, 50]"/>
                    </b-form-group>
                    <b-form-group :label="Trans.get('lang.search')" class="d-inline-block col-md mt-1">
                        <b-input-group>
                            <b-input placeholder="Search..." @keyup.enter="doSearch" v-model="searchString" />
                            <b-btn variant="secondary" @click="doSearch">
                                <i class="fi fi-rs-search"></i>
                            </b-btn>
                        </b-input-group>
                    </b-form-group>
                    <!-- <b-form-group :label="''" class="d-inline-block w-auto mt-1">

                    </b-form-group> -->
                </div>
            </b-card-body>
        </b-card>


        <b-card class="m-3" no-body>
            <!-- Table controls -->
            <b-card-body>
                <div class="d-flex justify-content-end">
                    <!-- <div>
                        <b-form-group :label="Trans.get('pagination.per_page')" class="d-inline-block w-auto mt-1">
                            <b-select v-model="perPage" :options="[10, 20, 30, 40, 50]"/>
                        </b-form-group>
                        <b-form-group :label="Trans.get('lang.search')" class="d-inline-block w-auto mt-1">
                            <b-input placeholder="Search..." @keyup.enter="doSearch" v-model="searchString" />
                        </b-form-group>
                        <b-form-group :label="''" class="d-inline-block w-auto mt-1">
                            <b-btn variant="info" @click="doSearch" style="margin-top: -3px;">
                                <i class="fi fi-rs-search"></i>
                            </b-btn>
                        </b-form-group>
                    </div> -->
                    <b-dropdown
                        v-if="UserAuth.hasAccess(accessRuleKey+'.group', 'has_access') ||
                            UserAuth.hasAccess(accessRuleKey+'.levelGroup', 'has_access')"
                        class="w-50 w-md-auto"
                        variant="info btn-sm w-icon"
                        :right="!isRTL"
                    >
                        <template slot="button-content">
                            <i class="fi fi-rs-apps"></i><span>{{ Trans.get('lang.menu') }}</span>
                        </template>

                        <b-dropdown-item
                            v-if="UserAuth.hasAccess(accessRuleKey+'.group', 'has_access')"
                            :to="{name: 'role.group.list'}">
                            <i class="fi fi-rr-list"></i>
                            <span> {{ Trans.get('role.group.name') }}</span>
                        </b-dropdown-item>

                        <b-dropdown-item
                            v-if="UserAuth.hasAccess(accessRuleKey+'.levelGroup', 'has_access')"
                            :to="{name: 'role.levelGroup.list'}">
                            <i class="fi fi-rr-list"></i>
                            <span> {{ Trans.get('role.level_group.name') }}</span>
                        </b-dropdown-item>

                    </b-dropdown>
                    <router-link 
                        v-if="UserAuth.hasAccess(accessRuleKey, 'c')" 
                        class="btn btn-sm btn-primary w-icon w-50 w-md-auto"
                        :to="{ name: 'role.add' }">
                        <i class="fi fi-rs-add"></i>&nbsp; {{ Trans.get("role.rolelist.add_new_role") }}
                    </router-link>
                    <!-- <router-link v-if="UserAuth.hasAccess(accessRuleKey, 'r')" 
                        class="btn btn-sm btn-secondary w-icon w-md-auto" 
                        :to="{ name: 'systemuser.role' }">
                        <i class="fi fi-rs-add"></i>&nbsp; {{ Trans.get("role.systemuser.manage_role_group") }}
                    </router-link> -->
                </div>
            </b-card-body>
            <!-- / Table controls -->

            <!-- Table -->
            <!-- <hr class="border-light m-0" /> -->

            <div class="table-responsive mb-0">
                <b-table :items="listData.data" :fields="fields" :sort-by.sync="sortBy" :sort-desc.sync="sortDesc" :striped="true" :bordered="true" class="card-table">
                    <!-- <template v-slot:cell(tenant)="data">
                        {{ data.item.tenant ? data.item.tenant.name : "" }}
                    </template>

                    <template v-slot:cell(tenant_group)="data">
                        {{ data.item.tenant_group ? data.item.tenant_group.name : "" }}
                    </template> -->

                    <template v-slot:cell(role_code)="data">
                        <b-badge variant="outline-default">{{ data.item.role_code }}</b-badge>
                    </template>

                    <template v-slot:cell(role_group)="data">                        
                        <b-badge v-if="data.item.role_group" variant="outline-success">{{ data.item.role_group.name }}</b-badge>
                        <template v-else>not grouped</template>
                    </template>
                    <template v-slot:cell(role_type)="data">                        
                        <b-badge v-if="data.item.role_type==1" variant="outline-info">Standard</b-badge>
                        <b-badge v-else variant="outline-warning">Non-Login role</b-badge>
                    </template>

                    <template v-slot:cell(actions)="data">
                        <div class="d-flex align-items-center justify-content-center">
                            <!-- <b-btn variant="default btn-xs icon-btn md-btn-flat" v-b-tooltip.hover title="Edit"><i class="ion ion-md-create"></i></b-btn> -->
                            <router-link class="btn btn-success icon-btn btn-sm" :title="Trans.get('lang.edit')" :to="{ name: 'role.edit', params: { roleId: data.item.id } }" v-if="(UserAuth.hasAccess(accessRuleKey, 'u') && data.item.locked_data_mode!=2) || UserAuth.isWebdev()">
                                <span class="ion ion-md-create"></span>
                            </router-link>

                            <b-btn class="btn btn-danger icon-btn btn-sm" :title="Trans.get('lang.delete')" @click="deleteRole(data.item.id)" v-if="UserAuth.hasAccess(accessRuleKey, 'd') && data.item.locked_data_mode==0">
                                <span class="ion ion-md-close"></span>
                            </b-btn>
                            <!-- <b-dropdown variant="default btn-xs icon-btn md-btn-flat hide-arrow" :right="!isRTL">
                            <template slot="button-content">
                                <i class="ion ion-ios-settings"></i>
                            </template>
                            <b-dropdown-item @click="deleteRole(data.item.id)">Remove</b-dropdown-item>
                            </b-dropdown> -->
                        </div>
                    </template>
                </b-table>
            </div>

            <!-- Pagination -->
            <b-card-body class="pt-0 pb-3">
                <div class="row">
                    <div class="col-sm text-sm-left text-center pt-3">
                        <span class="text-muted" v-if="listData.count">{{ Trans.get("pagination.page_of", { curPage: curPage, totalPages: totalPages }) }}</span>
                    </div>
                    <div class="col-sm pt-3">
                        <b-pagination class="justify-content-center justify-content-sm-end m-0" v-if="listData.count" v-model="curPage" :total-rows="listData.count" :per-page="perPage" size="sm" />
                    </div>
                </div>
            </b-card-body>
            <!-- / Pagination -->
        </b-card>
    </div>
</template>

<style src="@/vendor/libs/vue-flatpickr-component/vue-flatpickr-component.scss" lang="scss"></style>

<script>
    import flatPickr from "node_modules/vue-flatpickr-component";

    export default {
        name: "pages-role-list",
        metaInfo() {
            return { title: this.pageTitle };
        },
        components: {
            flatPickr,
        },
        data() {
            return {
                accessRuleKey: "moduser.role",
                // START ----FI listing option
                sortBy: "id",
                sortDesc: false,
                perPage: 10,
                curPage: 1,
                searchString: "",
                loadParams: {},
                roleItems: {},
                filterRole: "all",
                filterStatus: "all",
                // END ---- listing option

                fields: [],

                isLoadingData: false,
            };
        },

        computed: {
            listData: {
                get() {
                    return this.$store.state.role.roleList;
                },
                set(value) {
                    this.$store.commit("role/setRoleList", value);
                },
            },
            pageTitle() {
                return this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.role.caption);
            },
            listRole() {
                return this.$store.state.role.roleList;
            },
            totalItems() {
                return this.usersData.length;
            },
            totalPages() {
                return Math.ceil(this.listData.count / this.perPage);
            },
            //            
            canEditRoleType() {
                return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_type') || this.UserAuth.isWebdev();
            },
        },
        watch: {
            curPage(v) {
                this.loadData(v, this.searchString, this.sortBy, this.sortDesc);
            },
            perPage(v) {
                this.loadData(this.curPage, this.searchString, this.sortBy, this.sortDesc);
            },
            sortBy(v) {
                this.loadData(this.curPage, this.searchString, v, this.sortDesc);
            },
            sortDesc(v) {
                this.loadData(this.curPage, this.searchString, this.sortBy, v);
            },
            filterRole(v) {
                this.loadData(this.curPage, this.searchString, this.sortBy, this.sortDesc);
            },
            filterStatus(v) {
                this.loadData(this.curPage, this.searchString, this.sortBy, this.sortDesc);
            },
            // searchString(v) {
            //     const val = v.toLowerCase();
            //     var that = this;
            //     clearTimeout(this.suggestTimeout);
            //     this.suggestTimeout = setTimeout(function() {
            //         that.loadData(1, val);
            //     }, 300);
            // },
        },
        methods: {
            doSearch() {
                this.loadData(this.curPage,this.searchString,this.sortBy,this.sortDesc);
            },
            loadData(curPage, q = "", orderBy = false, sortDesc = false) {
                if(this.isLoadingData)
                    return false;

                var offset = this.perPage * (curPage - 1);
                this.loadParams = {system_role: 0};

                this.loadParams.limit = this.perPage;
                this.loadParams.offset = offset;

                if (q != "") {
                    this.loadParams.q = q;
                }

                if (orderBy != false) {
                    this.loadParams.orderBy = orderBy;
                    this.loadParams.orderType = sortDesc ? "DESC" : "ASC";
                }

                if (this.filterRole != "all") {
                    this.loadParams.role_code = this.filterRole;
                }

                if (this.filterStatus != "all") {
                    this.loadParams.status = this.filterStatus;
                
                }

                this.Web.setLoadingPage(true);
                this.isLoadingData = true;
                if(this.canEditRoleType){
                    this.loadParams.role_type = 1;
                }else{
                    delete this.loadParams.role_type;
                }

                this.$store.dispatch("role/roleList", this.loadParams)
                    .then((res)=>{
                        this.isLoadingData = false;
                        this.Web.setLoadingPage(false);
                        // _.forEach(res.data,(v,i)=>{
                        //     v.roles = v.role.split(';');
                        // });
                        // this.listData.data = res.data;
                        // this.$store.commit("user/setUserList", this.listData);
                        // console.log('data : ',this.listData);
                    });
            },
            deleteRole(roleId) {
                if (!this.UserAuth.hasAccess(this.accessRuleKey, "d")) {
                    //goto dashboard current tenant
                    this.Web.goToCurrentTenant();
                    this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), style: "warning" });
                    return false;
                }

                this.Web.showAlert({
                    styleType: "modal",
                    style: "warning",
                    title: "Delete Confirmation",
                    text: "Are you sure ?",
                    modalButtonCancel: "No",
                    modalButtonOk: "Yes",
                    onOk: () => {
                        this.$store
                            .dispatch("role/delete", roleId)
                            .then((res) => {
                                this.Web.showAlert({ text: "Data deleted" });
                                this.loadData(1);
                            })
                            .catch((res) => {
                                this.Web.showAlert({ text: "Delete fail", style: "warning" });
                            });
                    },
                });
            },
            initView() {
                this.Web.setModule("moduser");

                this.Web.setNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
                // this.Web.appendNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.module.caption));

                this.Web.resetBreadcrumb();
                this.Web.addBreadcrumb(this.Trans.get('lang.home'));
                this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
                this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.role.caption));
                // this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.merchant.children.insurance.caption));

                this.Web.setBodyWithPadding(false);
                this.Web.setShow("moduser");
            },
        },
        created() {
            if (!this.UserAuth.hasAccess(this.accessRuleKey)) {
                //goto dashboard current tenant
                this.Web.goToCurrentTenant();
                this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), style: "warning" });
                return false;
            }
            this.initView();

            this.loadData(1);
            this.fields = [
                {
                    key: "id",
                    sortable: true,
                    tdClass: "align-middle",
                    tdAttr: {
                        "data-lable": "No"
                    }
                },

                {
                    key: "name"
                    , label: this.Trans.get("role.field_caption.name"),
                    sortable: true,
                    tdClass: "align-middle",
                    tdAttr: {
                        "data-lable": this.Trans.get("role.field_caption.name")
                    }
                },

                {
                    key: "level",
                    label: this.Trans.get("role.field_caption.level"),
                    sortable: true,
                    tdClass: "align-middle",
                    tdAttr: {
                        "data-lable": this.Trans.get("role.field_caption.level")
                    }
                },

                // {
                //     key: "tenant",
                //     label: this.Trans.get("role.field_caption.tenant"),
                //     sortable: true,
                //     tdClass: "align-middle",
                //     tdAttr: {
                //         "data-lable": this.Trans.get("role.field_caption.tenant")
                //     }
                // },

                // {
                //     key: "tenant_group",
                //     label: this.Trans.get("role.field_caption.tenant_group"),
                //     sortable: true,
                //     tdClass: "align-middle",
                //     tdAttr: {
                //         "data-lable": this.Trans.get("role.field_caption.tenant_group")
                //     }
                // },

                {
                    key: "role_code",
                    sortable: true,
                    tdClass: "align-middle",
                    tdAttr: {
                        "data-lable": this.Trans.get("")
                    }
                },
                {
                    key: "role_group",
                    sortable: true,
                    tdClass: "align-middle",
                    tdAttr: {
                        "data-lable": this.Trans.get("")
                    }
                },
                {
                    key: "role_type",
                    sortable: true,
                    tdClass: "align-middle",
                    tdAttr: {
                        "data-lable": this.Trans.get("")
                    }
                },
                {
                    key: "actions",
                    label: " ",
                    sortable: false,
                    tdClass: "text-nowrap text-center",
                    thClass: "align-middle text-center"
                },
            ];

            if (!(this.UserAuth.hasAccess(this.accessRuleKey, "u") || this.UserAuth.hasAccess(this.accessRuleKey, "d"))) {
                this.fields.splice(6, 1);
            }

            if(!this.canEditRoleType)
                this.fields.splice(5, 1);

            //jika tidak menggunakan system tenant maka hilangkan kolom tenant
            if (this.AppConfig.system.multitenant.active == 0) {
                // this.fields.splice(3, 2);
            } else {
                // if (this.AppConfig.packageLocal.moduser.role_hidden_field.includes("tenant_group")) {
                //     this.fields.splice(4, 1);
                // }
                // if (this.AppConfig.packageLocal.moduser.role_hidden_field.includes("tenant")) {
                //     this.fields.splice(3, 1);
                // }
                if (this.AppConfig.packageLocal.moduser.role_hidden_field.includes("level")) {
                    this.fields.splice(2, 1);
                }
            }
        },
    };
</script>
