<template>
    <div>
        
        <header-breadcrumb
            :pageTitle="pageTitle"
            :showBack="false"
            :breadcrumbToTopWindow="true"
        />

        <b-container>
            <b-card class="my-4" no-body>
                <b-card-body>
                    <div class="d-flex justify-content-between">
                        <div class="d-flex w-100 w-xl-auto">
                            <h5>{{ Trans.get('coop_master.form_config_dashboard.name') }}</h5>
                        </div>
                        <div class="d-flex w-100 w-xl-auto">
                            <b-btn
                                variant="success btn-sm w-icon w-100 w-auto-xl"
                                @click="showForm()"
                            >
                                <i class="fi fi-rs-add"></i>
                                <span>{{ Trans.get("lang.add_attribute", { attribute: "Role"}) }}</span>
                            </b-btn>
                        </div>
                    </div>
                </b-card-body>

                 <!-- Table data -->
                 <div class="table-responsive mb-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.role') }}</th>
                                <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.feature') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(v, i) in listData" :key="'dashboard.listdata-' + i">
                                <td>
                                    <b>{{ getRole[v.key] }}</b>
                                </td>
                                <td>
                                    <ul v-for="(v2, i2) in JSON.parse(v.value)" :key="'dashboard.listdata-' + i + '-' + i2">
                                        <li>
                                            {{ Trans.get('coop_master.form_config_dashboard.input_caption.feature_list.'+i2) }}
                                            [<b>{{ v2 ? 'TAMPIL' : 'TIDAK TAMPIL' }}</b>]
                                        </li>
                                    </ul>
                                </td>
                                <td style="width: 80px;" class="text-center">
                                    <b-btn
                                        @click="showForm(false, v)"
                                        class="btn-dark btn-sm icon-btn md-btn-flat"
                                        v-b-tooltip.hover.left
                                        :title="Trans.get('lang.edit')"
                                    >
                                        <i class="fi fi-rs-edit"></i>
                                    </b-btn>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                 </div>
            </b-card>
        </b-container>

        <!-- Modal template -->
        <b-modal
            id="modals-form"
            :size="'md'"
            @ok="formSubmitted"
            centered no-fade
            ok-variant="sm btn-success"
            cancel-variant="sm btn-secondary">
            
            <div slot="modal-title">
                Form <span class="font-weight-light">{{ Trans.get('coop_master.form_config_dashboard.name') }}</span>
            </div>

            <!-- Role -->
            <b-form-group
                :label="Trans.get('coop_master.form_config_dashboard.input_caption.role')"
                label-align-md="right"
                label-class="pr-md-3"
                :label-cols-md="3"
                :dissabled="!isAdd"
            >

                <v-single-select
                    :modelData="form.role"
                    @onSelect="form.role = $event"
                    :options="listRole"
                    :placeholder="Trans.get('coop_master.form_config_dashboard.input_caption.role')"
                />

            </b-form-group>

            <!-- Features -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ Trans.get('coop_master.form_config_dashboard.input_caption.feature') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(v, i) in form.features" :key="'dashboard.listfeature-' + i">
                        <td>{{ Trans.get('coop_master.form_config_dashboard.input_caption.feature_list.'+i) }}</td>
                        <td>
                            <b-form-checkbox
                                :id="'checkbox-'+i"
                                v-model="form.features[i]"
                                :name="'checkbox-'+i"
                                :value="true"
                                :unchecked-value="false"
                            >
                                {{ Trans.get('coop_master.form_config_dashboard.input_caption.show') }}
                            </b-form-checkbox>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Modal footer -->
            <div slot="modal-footer">
                <b-button
                    variant="secondary"
                    class="w-icon btn-sm w-auto"
                    @click="$bvModal.hide('modals-form')">
                    <i class="fi fi-rs-circle-xmark"></i>
                    <span>{{ Trans.get('lang.close') }}</span>
                </b-button>
                <b-button
                    variant="success"
                    class="w-icon btn-sm w-auto"
                    @click="formSubmitted">
                    <i class="fi fi-rs-check-circle"></i>
                    <span>{{ Trans.get('lang.save') }}</span>
                </b-button>
            </div>

        </b-modal>

    </div>
</template>

<script>
    export default {
        name: "master-config-dashboard",
        data: () => ({
            accessRuleKey: "modcoop.master.config.dashboard",
            
            listData: [],
            configData:[],
            getRole: [],

            isAdd: true, //flag untuk form input, apakah proses add atau edit
            form: {},
            formEmpty: {
                role: '',
                features: {
                    info_pinjaman_jatuhtempo: false
                },
            },
            listRole: [],
            listFeature: [],
        }),
        watch: {},
        computed: {
            pageTitle() {
                return this.Trans.chose(this.AppConfig.packageLocal.modcoop.access.children.master.children.config.children.dashboard.caption);
            },
            configDb() {
                return this.$store.state.config.allConfig;
            }
        },
        methods: {
            loadData() {
                this.$store.dispatch("reloadConfig",{
                    'group[0]': 'dashboard.config.item',
                    'group[0]': 'dashboard.config',
                    saveState: false
                })
                .then((res) => {
                    this.listData = res;
                    this.configDb.forEach((v,k) => {
                        
                    });
                    this.configData = {};
                    this.listData = [];
                    res.forEach((v,k) => {
                        if (v.group == 'dashboard.config.item') {
                            this.listData.push(v.value);
                        }else if(v.group == 'dashboard.config') {
                            this.listData[''] = v.value;

                        }
                        this.data.registration.tos.form[v.key] = JSON.parse(JSON.stringify(v.value));
                    });
                    this.Web.setLoadingPage(false);
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text: this.Trans.get("alert.read_failed", { attribute: this.Trans.get("ac.config") }) + "<br>\n" + res.message,
                        type: "warning",
                    });
                    this.Web.setLoadingPage(false);
                });
            },
            loadListRole() {
                this.$store.dispatch("modcoopMasterPengurus/readListRole", {
                    params: {
                        level: ['!=', 0],
                        limit: 0
                    }
                }).then(res => {
                    this.getRole = [];
                    this.listRole = []
                    _.forEach(res.data,(v,k)=>{
                        if (v.level != 99) {
                            this.listRole.push({
                                text: '[' + v.level + '] - ' + v.name,
                                value: v.level,
                            });
                        }
                        this.getRole['role_'+v.level] = v.name;
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
            showForm(isAdd = true, v = false) {

                this.isAdd = isAdd;
                if (isAdd !== true && v !== false) {
                    this.form.role = v.key.replace('role_', '');
                    this.form.features = JSON.parse(v.value);
                } else {
                    this.form = JSON.parse(JSON.stringify(this.formEmpty));
                }

                this.$bvModal.show("modals-form");
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

                var data = JSON.parse(JSON.stringify(this.form));

                this.Web.setLoadingPage(true);

                this.$store
                    .dispatch("role/updateConfigDashboard", {
                        data: data
                    })
                    .then((res) => {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.success_title"),
                            text: this.Trans.get("alert.update_success", { attribute: this.Trans.get("ac.config") }),
                            type: "success",
                        });

                        this.loadData();
                        this.$bvModal.hide("modals-form");
                        this.Web.setLoadingPage(false);
                    })
                    .catch((res) => {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.warning_title"),
                            text: this.Trans.get("alert.update_failed", { attribute: this.Trans.get("ac.config") }) + "<br>\n" + res.message,
                            type: "warning",
                        });
                        this.Web.setLoadingPage(false);
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
            if (!this.UserAuth.hasAccess(this.accessRuleKey, 'has_access') && (!this.userAuthIsWebdevLevel || !this.userAuthIsSmartcoopAdmin)) {
                //goto dashboard current tenant
                this.Web.goToCurrentTenant();
                this.Web.showAlert({
                    title: this.Trans.get("alert.warning_title"),
                    text: this.Trans.get("alert.access_denied"),
                    type: "warning"
                });
                return false;
            }

            this.form = JSON.parse(JSON.stringify(this.formEmpty));

            this.initView();
            this.loadData();
            this.loadListRole();
        }
    }
</script>