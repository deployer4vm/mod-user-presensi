<template>
    <div>
        <header-breadcrumb 
            :pageTitle="pageTitle" 
            :showBack="false"
        />
        
        <b-container fluid>
            <!-- Filter dll -->
            <b-card class="my-3" no-body>
                <b-card-body>
                    <div class="d-flex flex-column flex-md-row justify-content-between">
                        <div class="d-flex flex-wrap flex-md-nowrap align-items-center">
                            <b-btn 
                                v-b-toggle.filter-block
                                variant="default btn-sm w-icon btn-collapse"
                                :title="Trans.get('lang.filter')"
                            >
                                <i class="fi fi-rs-filter"></i>
                                <span>{{ Trans.get('lang.filter') }}</span>
                            </b-btn>
                        </div>
                        <div class="d-flex flex-wrap flex-md-nowrap align-items-center">
                            <router-link 
                                v-if="UserAuth.hasAccess(accessRuleKey, 'c')" 
                                class="btn btn-sm btn-primary w-icon w-50 w-md-auto" 
                                :to="{ name: 'systemuser.add' }"
                                :title="Trans.get('user.userlist.add_new_user')"
                            >
                                <i class="fi fi-rs-add"></i>
                                <span>{{ Trans.get("user.userlist.add_new_user") }}</span>
                            </router-link>
                            <router-link 
                                v-if="UserAuth.hasAccess(accessRuleKey, 'r')" 
                                class="btn btn-sm btn-secondary w-icon w-50 w-md-auto" 
                                :to="{ name: 'systemuser.role' }"
                                :title="Trans.get('user.systemuser.role_list')"
                            >
                                <i class="fi fi-rr-list"></i>
                                <span>{{ Trans.get("user.systemuser.role_list") }}</span>
                            </router-link>
                        </div>
                    </div>
    
                    <b-collapse id="filter-block">
                        <hr />
                        <b-row>
                            <b-col md="6" xl>
                                <b-form-group :label="Trans.get('pagination.per_page')">
                                    <b-select v-model="perPage" :options="[10, 20, 30, 40, 50]" class="form-control"/>
                                </b-form-group>
                            </b-col>
                            <b-col md="6" xl>
                                <b-form-group :label="Trans.get('user.field_caption.role')">
                                    <b-select v-model="filterRole" :options="roleItems" class="form-control"/>
                                </b-form-group>
                            </b-col>
                            <b-col md="6" xl>
                                <b-form-group :label="Trans.get('user.field_caption.status')">
                                    <b-select v-model="filterStatus" :options="{
                                        'all': Trans.get('lang.view_all'),
                                        '1': Trans.get('user.field_caption.status_item.active'),
                                        '2': Trans.get('user.field_caption.status_item.banned')
                                    }" class="form-control"/>
                                </b-form-group>
                            </b-col>
                            <!-- Pencarian -->
                            <b-col md="6" xl>
                                <b-form-group :label="Trans.get('lang.search')">
                                    <b-input-group>
                                        <b-input
                                            placeholder="Search..." @keyup.enter="doSearch" v-model="searchString"/>
                                            <b-input-group-append>
                                                <b-btn
                                                    variant="secondary"
                                                    @click="doSearch"
                                                    class="btn-icon"
                                                    :title="Trans.get('lang.search')"
                                                >
                                                    <i class="fi fi-rs-search"></i>
                                                </b-btn>
                                            </b-input-group-append>
                                    </b-input-group>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-collapse>
                </b-card-body>
            </b-card>
            <!-- / Filter dll -->

            <!-- Data -->
            <b-card class="my-3" no-body>
                <!-- Header -->
				<b-card-header>
					<div class="d-flex justify-content-between align-items-center flex-wrap">
						<h5 class="my-1">
                            Manage API Client
                        </h5>
					</div>
				</b-card-header>
                <!-- / Header -->

                <!-- Table -->
                <div class="table-responsive mb-0">
                    <b-table
                        :items="listData.data"
                        :fields="fields"
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        :striped="true"
                        hover
                        class="card-table"
                    >
                        <template v-slot:cell(account)="data">
                            <a href="javascript:void(0)">{{ data.item.account }}</a>
                        </template>

                        <template v-slot:cell(actions)="data">
                            <div class="d-flex align-items-center justify-content-center">
                                <!-- <b-btn variant="default btn-xs icon-btn md-btn-flat" v-b-tooltip.hover title="Edit"><i class="fi fi-rs-edit"></i></b-btn> -->
                                <router-link 
                                    class="btn btn-dark icon-btn btn-sm md-btn-flat" 
                                    :title="Trans.get('lang.edit')" 
                                    v-b-tooltip.hover 
                                    :to="{ name: 'systemuser.edit', params: { userId: data.item.id } }" 
                                    v-if="UserAuth.hasAccess(accessRuleKey, 'u')"
                                >
                                    <i class="fi fi-rs-edit"></i>
                                </router-link>
                                <b-btn 
                                    class="btn btn-danger icon-btn btn-sm md-btn-flat" 
                                    :title="Trans.get('lang.delete')" 
                                    @click="deleteUser(data.item.id)" 
                                    v-if="UserAuth.hasAccess(accessRuleKey, 'd') && data.item.linked_id == 0" 
                                    v-b-tooltip.hover
                                >
                                    <i class="fi fi-rs-trash"></i>
                                </b-btn>

                                <b-btn 
                                    class="btn btn-info btn-xs w-icon md-btn-flat" 
                                    v-if="data.item.api_token" 
                                    v-clipboard:copy="data.item.api_token.api_token"
                                    v-clipboard:success="copySuccess"
                                    v-clipboard:error="copyFail"
                                    title="Copy Token"
                                >   
                                    <i class="fi fi-rs-copy"></i>
                                    <span>Copy Token</span>
                                </b-btn>
                                <b-btn 
                                    class="btn btn-primary btn-xs w-icon md-btn-flat"
                                    v-if="!data.item.api_token"
                                    @click="generateToken(data.item.id)"
                                    title="Generate Token"
                                >
                                    <i class="fi fi-rs-refresh"></i>
                                    <span>Generate Token</span>
                                </b-btn>
                            </div>
                            <!-- <b-dropdown variant="default btn-xs icon-btn md-btn-flat hide-arrow" :right="!isRTL">
                            <template slot="button-content">
                                <i class="ion ion-ios-settings"></i>
                            </template>
                            <b-dropdown-item href="javascript:void(0)">View profile</b-dropdown-item>
                            <b-dropdown-item @click="banUser(data.item.id)">Ban user</b-dropdown-item>
                            <b-dropdown-item @click="deleteUser(data.item.id)">Remove</b-dropdown-item>
                        </b-dropdown> -->
                        </template>
                    </b-table>
                </div>
                <!-- / Table -->

                <!-- Pagination -->
                <b-card-footer
                    v-if="listData.count"
                    class="flex-md-row flex-column justify-content-center justify-content-md-between align-items-center flex-wrap" 
                >
                    <span class="text-muted">
                        {{ Trans.get('pagination.page_of_data', {
                            'curPage' : curPage,
                            'totalPages' : totalPages,
                            'totalData' : Format.formatNumber(listData.count)
                        }) }}
                    </span>
                    <b-pagination
                        class="justify-content-center justify-content-sm-end m-0" 
                        v-model="curPage" 
                        :total-rows="listData.count" 
                        :per-page="perPage" 
                        size="sm" 
                    />
                </b-card-footer>
                <!-- / Pagination -->
            </b-card>
            <!-- / Data -->
        </b-container>
    </div>
</template>

<style src="@/vendor/libs/vue-flatpickr-component/vue-flatpickr-component.scss" lang="scss"></style>

<script>
import flatPickr from "node_modules/vue-flatpickr-component";
import VueClipboard from 'node_modules/vue-clipboard2'
Vue.use(VueClipboard)

export default {
    name: "pages-usersystem-list",

    metaInfo() {
        return { title: this.pageTitle };
    },

    components: {
        flatPickr
    },

    data: () => ({
        accessRuleKey: "moduser.system_user",
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
        defaultFields: [
            {
                key: "id",
                sortable: true,
                thStyle: "width: 50px",
                tdClass: "align-middle text-center",
                tdAttr: {
                    "data-lable": "ID"
                },
            },
            {
                key: "name",
                sortable: true, tdClass: "align-middle",
                tdAttr: {
                    "data-lable": "Name"
                }
            },
            {
                key: "actions",
                label: "Aksi",
                thStyle: "width: 220px",
                tdClass: "text-nowrap align-middle text-center col-action",
                tdAttr: {
                    "data-lable": ""
                }
            }
        ]
    }),

    computed: {
        listData: {
            get() {
                return this.$store.state.usersystem.userList;
            },
            set(value) {
                this.$store.commit("usersystem/setUserList", value);
            }
        },
        pageTitle() {
            return this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.system_user.caption);
        },
        listRole() {
            return this.$store.state.role.roleList;
        },
        totalItems() {
            return this.usersData.length;
        },
        totalPages() {
            return Math.ceil(this.listData.count / this.perPage);
        }
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
        searchString(v) {
            const val = v.toLowerCase();
            var that = this;
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(function() {
                that.loadData(1, val);
            }, 300);
        }
    },

    methods: {
        doSearch() {
            this.loadData(this.curPage,this.searchString,this.sortBy,this.sortDesc);
        },
        loadData(curPage, q = "", orderBy = false, sortDesc = false) {
            var offset = this.perPage * (curPage - 1);
            this.loadParams = {};
            this.loadParams.params = {};

            this.loadParams.params.limit = this.perPage;
            this.loadParams.params.offset = offset;
            this.loadParams.params.system_user = 1;

            if (q != "") {
                this.loadParams.params.q = q;
            }

            if (orderBy != false) {
                this.loadParams.params.orderBy = orderBy;
                this.loadParams.params.orderType = sortDesc ? "DESC" : "ASC";
            }

            if (this.filterRole != "all") {
                this.loadParams.params.role = this.filterRole;
            }

            if (this.filterStatus != "all") {
                this.loadParams.params.status = this.filterStatus;
            }

            this.$store.dispatch("usersystem/userList", this.loadParams);
        },
        setStatus(userId, status) {
            if (status == 1) {
            } else {
            }
        },
        deleteUser(userId) {
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
                        .dispatch("usersystem/delete", userId)
                        .then(res => {
                            this.Web.showAlert({ text: "Data deleted" });
                            this.loadData(1);
                        })
                        .catch(res => {
                            this.Web.showAlert({ text: "Delete fail", style: "warning" });
                        });
                }
            });
        },
        copySuccess() {
            this.Web.showAlert({ text: "Copy success" });
            this.loadData(1);
        },
        copyFail() {
            this.Web.showAlert({ text: "Copy fail" });
        },
        generateToken(id)
        {
            this.Web.showAlert({
                styleType: "modal",
                style: "warning",
                title: "Generate Token",
                text: "Generate ulang token ?<br><b>Token lama akan terhapus secara permanen.</b>",
                modalButtonCancel: "No",
                modalButtonOk: "Yes",
                onOk: () => {
                    this.$store.dispatch("usersystem/generateToken", id)
                        .then(res => {
                            this.Web.showAlert({ text: "Token generated" });
                            this.loadData(1);
                        })
                        .catch(res => {
                            this.Web.showAlert({ text: "Token generated fail", style: "warning" });
                        });
                }
            });
        },
        initView() {
            this.Web.setModule("moduser");

            this.Web.setNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.system_user.caption));
            // this.Web.appendNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.module.children.system_user.caption));

            this.Web.resetBreadcrumb();
            this.Web.addBreadcrumb(this.Trans.get('lang.home'));
            this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
            this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.system_user.caption));
            // this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.merchant.children.insurance.children.system_user.caption));

            this.Web.setBodyWithPadding(false);
            this.Web.setShow("moduser");
        }
    },

    created() {
        if (!this.UserAuth.hasAccess(this.accessRuleKey, 'r')) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning"
            });
            return false;
        }
        this.initView();

        //load data usersystem
        this.loadData(1);

        //load data role
        this.$store.dispatch("rolesystem/roleList").then(res => {
            let tmpRoleItems = { "all": this.Trans.get("lang.view_all") };
            _.forEach(res.data, (v, i) => {
                tmpRoleItems[v.role_code] = "[" + v.role_code + "] " + v.name;
            });
            this.roleItems = tmpRoleItems;
        });

        this.fields = this.defaultFields;

        if (!(this.UserAuth.hasAccess(this.accessRuleKey, "u") || this.UserAuth.hasAccess(this.accessRuleKey, "d"))) {
            this.fields.splice(6, 1);
        }
    }
};
</script>
