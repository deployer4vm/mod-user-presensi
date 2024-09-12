<template>
    <div>
        <header-breadcrumb 
            :pageTitle="pageTitle" 
            :showBack="true" 
            :backPath="{ name: 'role.list' }"
        />

        <b-container fluid>
            <b-card class="my-3" no-body>
                <b-card-body>
                    <div class="d-flex flex-column flex-md-row justify-content-between">
                        <div class="d-flex flex-wrap flex-md-nowrap align-items-center">
                            <b-btn
                                v-b-toggle.filter-block
                                variant="default w-icon btn-sm btn-collapse">
                                <i class="fi fi-rs-filter"></i>
                                <span>{{ Trans.get("lang.filter") }}</span>
                            </b-btn>
                        </div>
                        <div class="d-flex flex-wrap flex-md-nowrap align-items-center">
                            <b-btn
                                v-if="UserAuth.hasAccess(data.accessRuleKey, 'c')"
                                variant="success btn-sm w-icon"
                                @click="showForm()"
                            >
                                <i class="fi fi-rs-add"></i>
                                <span>
                                    {{ Trans.get("lang.add_attribute", { 
                                            attribute: Trans.get("role.level_group.data_level.name")
                                        })
                                    }}
                                </span>
                            </b-btn>
                        </div>
                    </div>

                    <b-collapse id="filter-block">
                        <hr>
                        <b-row>
                            <b-col>
                                <b-form-group :label="Trans.get('pagination.per_page')">
                                    <b-select
                                        v-model="data.listData.perPage"
                                        :options="data.listData.perPageOption"
                                        class="form-control"
                                    />
                                </b-form-group>
                            </b-col>
                            <b-col>
                                <b-form-group :label="Trans.get('lang.search')">
                                    <b-input-group>
                                        <b-input
                                            :placeholder="Trans.get('lang.keyword')"
                                            @keyup.enter="doSearch()"
                                            v-model="data.listData.searchString"
                                        />
                                        <b-input-group-append>
                                            <b-btn variant="secondary" @click="doSearch" class="btn-sm">
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

            <b-card class="my-3" no-body>
                <!-- Header -->
				<b-card-header>
					<div class="d-flex justify-content-between align-items-center flex-wrap">
						<h5 class="my-1">
                            {{ Trans.get('role.level_group.table_level.name') }}
                        </h5>
					</div>
				</b-card-header>
                <!-- / Header -->

                <!-- Table -->
                <div class="table-responsive mb-0">
                    <b-table 
                        :items="listData.data" 
                        :fields="fields" 
                        :sort-by.sync="data.listData.sortBy" 
                        :sort-desc.sync="data.listData.sortDesc" 
                        :striped="true" 
                        :hover="true" 
                        :small="true" 
                        :responsive="true" 
                        class="card-table mb-0" 
                        :empty-text="data.listData.noResultsText" 
                        show-empty
                    >
                        <template v-slot:empty="scope">
                            <h5 class="text-center mb-1 mt-1">{{ scope.emptyText }}</h5>
                        </template>
                        
                        <template v-slot:cell(no)="row">
                            {{ row.index + 1 + (data.listData.curPage - 1) * data.listData.perPage }}
                        </template>

                        <template v-slot:cell(code)="row">
                            <b-badge variant="outline-secondary">{{ row.item.code }}</b-badge>
                        </template>

                        <template v-slot:cell(level)="row">
                            <b-badge variant="outline-info">{{ row.item.level_start }} s/d {{ row.item.level_end }}</b-badge>
                        </template>

                        <template v-slot:cell(actions)="row">
                            <div class="d-flex align-items-center justify-content-center">
                                <b-btn
                                    v-if="(UserAuth.hasAccess(data.accessRuleKey, 'u') && row.item.locked_data_mode != 2) || UserAuth.isWebdev()"
                                    @click="showForm(false, row.item.id)"
                                    variant="dark btn-sm icon-btn md-btn-flat"
                                    v-b-tooltip.hover.left
                                    :title="Trans.get('lang.edit')"
                                >
                                    <i class="fi fi-rs-edit"></i>
                                </b-btn>
                                <b-btn
                                    v-if="UserAuth.hasAccess(data.accessRuleKey, 'd') && row.item.locked_data_mode == 0"
                                    @click="deleteData(row.item.id)"
                                    variant="danger btn-sm icon-btn md-btn-flat"
                                    :title="Trans.get('lang.delete')"
                                >
                                    <i class="fi fi-rs-trash"></i>
                                </b-btn>
                            </div>
                        </template>
                    </b-table>
                </div>

                <!-- Pagination -->
                <b-card-footer 
                    v-if="listData.count"
                    class="flex-md-row flex-column justify-content-center justify-content-md-between align-items-center flex-wrap" 
                >
                    <span class="text-muted">
                        {{ Trans.get('pagination.page_of_data', {
                            'curPage' : data.listData.curPage,
                            'totalPages' : totalPages,
                            'totalData' : Format.formatNumber(listData.count)
                        }) }}
                    </span>
                    <b-pagination 
                        v-if="totalPages>1"
                        class="justify-content-center justify-content-sm-end m-0" 
                        v-model="data.listData.curPage" 
                        :total-rows="listData.count" 
                        :per-page="data.listData.perPage" 
                        size="sm" 
                    />
                </b-card-footer>
                <!-- / Pagination -->
            </b-card>
        </b-container>

        <!-- modal form role level group -->
        <b-modal 
            id="modals-form" 
            :size="data.defaultModalSize" 
            @ok="formSubmitted" 
            @hidden="$v.$reset" 
            centered no-fade
        >
            <div slot="modal-title">
                {{ Trans.get('role.level_group.form_level.name') }} / 
                <span class="font-weight-light">
                    {{ Trans.get('lang.'+(data.formData.isAdd ? 'add' : 'edit')) }}
                </span>
            </div>

            <!-- code -->
            <b-form-group
                :label="Trans.get('role.level_group.data_level.field_name.code')"
                label-align-md="right"
                label-class="pr-md-3"
                :label-cols-md="3"
            >
                <b-input
                    v-model.trim="data.formData.form.code"
                    :placeholder="Trans.get('role.level_group.form_level.input_description.code')"
                    :state="$v.data.formData.form.code.$error ? false : null"
                    @change="$v.data.formData.form.code.$touch()"
                    :disabled="isDisabled"
                />

                <invalid-tooltip
                    :inputItem="$v.data.formData.form.code"
                    :fieldName="Trans.get('role.level_group.data_level.field_name.code')"
                />
            </b-form-group>

            <!-- name -->
            <b-form-group
                :label="Trans.get('role.level_group.data_level.field_name.name')"
                label-align-md="right"
                label-class="pr-md-3"
                :label-cols-md="3"
            >
                <b-input
                    v-model.trim="data.formData.form.name"
                    :placeholder="Trans.get('role.level_group.form_level.input_description.name')"
                    :state="$v.data.formData.form.name.$error ? false : null"
                    @change="$v.data.formData.form.name.$touch()"
                    :disabled="isDisabled"
                />

                <invalid-tooltip
                    :inputItem="$v.data.formData.form.name"
                    :fieldName="Trans.get('role.level_group.data_level.field_name.name')"
                />
            </b-form-group>

            <!-- level start -->
            <b-form-group
                :label="Trans.get('role.level_group.data_level.field_name.level_start')"
                label-align-md="right"
                label-class="pr-md-3"
                :label-cols-md="3"
            >
                <v-single-select
                    :modelData="data.formData.form.level_start"
                    @onSelect="data.formData.form.level_start = $event"
                    :options="selectLevel"
                    :placeholder="Trans.get('role.level_group.form_level.input_description.level_start')"
                />  
            </b-form-group>

            <!-- level end -->
            <b-form-group
                :label="Trans.get('role.level_group.data_level.field_name.level_end')"
                label-align-md="right"
                label-class="pr-md-3"
                :label-cols-md="3"
            >
                <v-single-select
                    :modelData="data.formData.form.level_end"
                    @onSelect="data.formData.form.level_end = $event"
                    :options="selectLevel"
                    :placeholder="Trans.get('role.level_group.form_level.input_description.level_end')"
                />  
            </b-form-group>

            <!-- keterangan -->
            <b-form-group
                :label="Trans.get('role.level_group.data_level.field_name.description')"
                label-align-md="right"
                label-class="pr-md-3"
                :label-cols-md="3"
                :content-cols-lg="9"
            >
                <b-textarea
					v-model.trim="data.formData.form.description"
					:placeholder="Trans.get('role.level_group.form_level.input_description.description')"
					:rows="3"
					:max-rows="6"
                    :disabled="isDisabled"
				/>
            </b-form-group>

            <!-- locked -->
            <b-form-group
                v-if="canEditLockedDataMode"
                :label="Trans.get('role.level_group.data_level.field_name.locked_data_mode')"
                label-align-md="right"
                label-class="pr-md-3"
                :label-cols-md="3"
            >
                <b-select 
                    v-model="data.formData.form.locked_data_mode" 
                    :options="selectLockedDataMode"
                    class="form-control"
                />
            </b-form-group>

            <!-- Modal footer -->
            <div slot="modal-footer">
                <b-button
                    variant="secondary"
                    class="w-icon btn-sm"
                    @click="$bvModal.hide('modals-form')">
                    <i class="fi fi-rs-circle-xmark"></i>
                    <span>{{ Trans.get('lang.close') }}</span>
                </b-button>
                <b-button
                    variant="success"
                    class="w-icon btn-sm"
                    @click="formSubmitted">
                    <i class="fi fi-rs-check-circle"></i>
                    <span>{{ Trans.get('lang.save') }}</span>
                </b-button>
            </div>
        </b-modal>
        <!-- / modal form site -->
    </div>
</template>

<script>
import { required } from "node_modules/vuelidate/lib/validators";

export default {
    name: "role-level-group-list",

    validations: {
        data: {            
            formData: {
                form: {
                    code: {
                        required
                    },
                    name: {
                        required
                    }
                }
            }
        },
    },

    data: () => ({
        selectLevel: [],
        selectLockedDataMode: [],
    }),

    watch: {
        'data.listData.curPage'(v) {
            this.loadList(
                v, 
                this.data.listData.searchString, 
                this.data.listData.sortBy, 
                this.data.listData.sortDesc
            );
        },
        'data.listData.perPage'(v) {
            this.loadList(
                this.data.listData.curPage, 
                this.data.listData.searchString, 
                this.data.listData.sortBy, 
                this.data.listData.sortDesc
            );
        },
        'data.listData.sortBy'(v) {
            this.loadList(
                this.data.listData.curPage, 
                this.data.listData.searchString, 
                v, 
                this.data.listData.sortDesc
            );
        },
        'data.listData.sortDesc'(v) {
            this.loadList(
                this.data.listData.curPage, 
                this.data.listData.searchString, 
                this.data.listData.sortBy, 
                v
            );
        },
        'data.listData.searchString'(v) {
            let val = v.toLowerCase();
            var that = this;
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(function () {
                that.loadList(1, val);
            }, 300);
        },
    },

    computed: {
        data: {
            get() {
                return this.$store.state.moduserView.dataRoleLevelGroup;
            },
            set(value) {
                this.$store.commit("moduserView/setDataRoleLevelGroup", value);
            }
        },
        listData() {
            return this.Repo('moduserRoleLevelGroup').listData;
        },  
        oneData() {
            return this.Repo('moduserRoleLevelGroup').oneData;
        },
        pageTitle() {
            return this.Trans.get("role.level_group.name");
        },
        totalPages() {
            return Math.ceil(this.listData.count / this.data.listData.perPage);
        },
        fields() {
            return [
                {
                    key: "no",
                    sortable: false,
                    thClass: "text-center",
                    thStyle: "width: 50px",
                    tdClass: "text-center text-nowrap",
                },
                {
                    key: "code",
                    label: this.Trans.get('role.level_group.data_level.field_name.code'),
                    thClass: "text-left",
                    tdClass: "text-left"
                },
                {
                    key: "name",
                    label: this.Trans.get('role.level_group.data_level.field_name.name'),
                    thClass: "text-left",
                    tdClass: "text-left"
                },
                {
                    key: "level",
                    label: this.Trans.get('role.level_group.table_level.column_name.level'),
                    thClass: "text-left",
                    tdClass: "text-left"
                },
                {
                    key: "description",
                    label: this.Trans.get('role.level_group.data_level.field_name.description'),
                    thClass: "text-left",
                    tdClass: "text-left"
                },
                {
                    key: "actions",
                    label: "",
                    thClass: "text-center",
                    thStyle: "width: 100px",
                    tdClass: "text-center text-nowrap",
                },
            ];
        },
        canEditLockedDataMode() {
            return this.UserAuth.hasAccess(this.data.accessRuleKey + '.can_edit_locked_data_mode') || this.UserAuth.isWebdev();
        },
        isDisabled() {
            return !this.data.formData.isAdd && this.data.formData.form.locked_data_mode == 2;
        },
    },

    methods: {
        doSearch() {
            this.loadList(
                this.data.listData.curPage, 
                this.data.listData.searchString, 
                this.data.listData.sortBy, 
                this.data.listData.sortDesc
            );
        },
        loadList(curPage = 1, q = "", orderBy = false, sortDesc = false) {
            var offset = this.data.listData.perPage * (curPage - 1);

            this.data.listData.loadParams.limit = this.data.listData.perPage;
            this.data.listData.loadParams.offset = offset;

            if (q != "") {
                this.data.listData.loadParams.q = q;
            } else {
                delete this.data.listData.loadParams.q;
            }

            if (orderBy != false) {
                this.data.listData.loadParams.orderBy = orderBy;
                this.data.listData.loadParams.orderType = sortDesc ? "DESC" : "ASC";
            } else {
                this.data.listData.loadParams.orderBy = this.data.listData.sortBy;
                this.data.listData.loadParams.orderType =  this.data.listData.sortDesc ? 'DESC' : 'ASC';
            }

            this.Repo('moduserRoleLevelGroup')
                .readList({
                    params: this.data.listData.loadParams 
                })
                .then((res) => {
                    if (this.listData.count == 0) {
                        this.data.listData.noResultsText = this.Trans.get("lang.no_data");
                        this.Web.showAlert({
                            title: this.Trans.get("alert.info_title"),
                            text: this.Trans.get("lang.no_data"),
                            type: "info",
                        });
                    }
                })
                .catch((res) => {
                    this.data.listData.noResultsText = this.Trans.get("lang.no_data");
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.read_failed", {
                                attribute: this.Trans.get("role.level_group.data_level.name"),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },
        showForm(isAdd = true, id = 0) {
            this.data.formData.isAdd = isAdd;
            if (!isAdd) {
                this.Repo('moduserRoleLevelGroup')
                    .readOne({ id: id })
                    .then((res) => {
                        this.data.formData.form = this.oneData;
                        this.$bvModal.show("modals-form");
                    })
                    .catch((res) => { 
                        this.Web.showAlert({
                            title: this.Trans.get("alert.warning_title"),
                            text:
                                this.Trans.get("alert.read_failed", {
                                    attribute: this.Trans.get("role.level_group.data_level.name"),
                                }) +
                                "<br>\n" +
                                res.message,
                            type: "warning",
                        });
                    });
            } else {
                this.data.formData.form = JSON.parse(JSON.stringify(this.data.formData.formEmpty));
                this.$bvModal.show("modals-form");
            }
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
            
            var data = JSON.parse(JSON.stringify(this.data.formData.form));

            if (this.data.formData.isAdd) {
                this.saveData(data);
            } else {
                this.updateData(data);
            }
        },
        saveData(data) {
            if (!this.UserAuth.hasAccess(this.data.accessRuleKey, "c")) {
                //goto dashboard current tenant
                this.Web.goToCurrentTenant();
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.access_denied"),
                    type: "warning",
                });
                return false;
            }

            this.Web.setLoadingPage(true);
            this.Repo('moduserRoleLevelGroup')
                .create({ data: data })
                .then((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.success_title"),
                        text: this.Trans.get("alert.create_success", {
                            attribute: this.Trans.get("role.level_group.data_level.name"),
                        }),
                        type: "success",
                    });
                    this.$bvModal.hide("modals-form");
                    this.doSearch();
                    this.Web.setLoadingPage(false);
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.create_failed", {
                                attribute: this.Trans.get("role.level_group.data_level.name"),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                    this.Web.setLoadingPage(false);
                });
        },
        updateData(data) {
            if (!this.UserAuth.hasAccess(this.data.accessRuleKey, "u")) {
                //goto dashboard current tenant
                this.Web.goToCurrentTenant();
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.access_denied"),
                    type: "warning",
                });
                return false;
            }

            this.Web.setLoadingPage(true);
            this.Repo('moduserRoleLevelGroup').update({
                    id: data.id,
                    data: data,
                })
                .then((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.success_title"),
                        text: this.Trans.get("alert.update_success", {
                            attribute: this.Trans.get("role.level_group.data_level.name"),
                        }),
                        type: "success",
                    });
                    this.$bvModal.hide("modals-form");
                    this.doSearch();
                    this.Web.setLoadingPage(false);
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.update_failed", {
                                attribute: this.Trans.get("role.level_group.data_level.name"),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                    this.Web.setLoadingPage(false);
                });
        },
        deleteData(id) {
            if (!this.UserAuth.hasAccess(this.data.accessRuleKey, "d")) {
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
                    this.Repo('moduserRoleLevelGroup').delete({ id: id })
                        .then((res) => {
                            this.Web.showAlert({
                                title: this.Trans.get("alert.success_title"),
                                text: this.Trans.get("alert.delete_success", {
                                    attribute: this.Trans.get("role.level_group.data_level.name"),
                                }),
                                type: "success",
                            });
                            this.doSearch();
                            this.Web.setLoadingPage(false);
                        })
                        .catch((res) => {
                            this.Web.showAlert({
                                title: this.Trans.get("alert.warning_title"),
                                text:
                                    this.Trans.get("alert.delete_failed", {
                                        attribute: this.Trans.get("role.level_group.data_level.name"),
                                    }) +
                                    "<br>\n" +
                                    res.message,
                                type: "warning",
                            });
                        });
                },
            });
        },
        //
        initView() {
            this.Web.setModule("moduser");

            this.Web.setNavbarTitle(this.pageTitle);

            this.Web.resetBreadcrumb();
            this.Web.addBreadcrumb(this.Trans.get("lang.home"));
            this.Web.addBreadcrumb(this.Trans.get("user.name"));
            this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.role.caption), {
                name: 'role.list' 
            });
            this.Web.addBreadcrumb(this.pageTitle);

            this.Web.setBodyWithPadding(false);
            this.Web.setShow("moduser"); 
        },        
    },

    created() {
        if (!this.UserAuth.hasAccess(this.data.accessRuleKey)) {
            //goto dashboard current tenant
            this.Web.goToCurrentTenant();
            this.Web.showAlert({
                title: this.Trans.get("alert.warning_title"),
                text: this.Trans.get("alert.access_denied"),
                type: "warning",
            });
            return false;
        }

        for (let i = 0; i <= 99; i++) {
            this.selectLevel.push({
                value: i,
                text: 'Level '+i
            });
        }

        this.selectLockedDataMode = [
            {text:this.Trans.get('role.level_group.data_level.label.locked_data_mode_0'), value:0},
            {text:this.Trans.get('role.level_group.data_level.label.locked_data_mode_1'), value:1},
            {text:this.Trans.get('role.level_group.data_level.label.locked_data_mode_2'), value:2},
        ];

        //reload list data
        this.initView();
        this.loadList();
    },
}
</script>