<template>
    <div>
        <header-breadcrumb :pageTitle="pageTitle" :showBack="false" />

        <b-container>
            <b-card class="my-4" no-body>
                <b-card-header>
                    <b-card-title class="mb-0">
                        {{ pageTitle }}
                    </b-card-title>
                </b-card-header>

                <b-card-body>
                    <!-- Nama Config -->
                    <b-form-group
                        :label="Trans.get('user_config.form_dashboard.input_caption.name')"
                        label-align-md="right"
                        label-class="pr-md-2"
                        :label-cols-md="3"
                    >
                        <b-input
                            v-model="form.name"
                            :placeholder="Trans.get('user_config.form_dashboard.input_caption.name')"
                            :state="!$v.form.name.$error ? null : false"
                        />
                        <invalid-tooltip
                            :inputItem="$v.form.name"
                            :fieldName="Trans.get('user_config.form_dashboard.input_caption.name')"
                        />
                    </b-form-group>

                    <!-- Deskripsi -->
                    <b-form-group
                        :label="Trans.get('user_config.form_dashboard.input_caption.description')"
                        label-align-md="right"
                        label-class="pr-md-2"
                        :label-cols-md="3"
                    >
                        <b-textarea
                            v-model="form.description"
                            :placeholder="Trans.get('user_config.form_dashboard.input_caption.description')"
                        />
                    </b-form-group>

                    <!-- Template Code -->
                    <b-form-group
                        :label="Trans.get('user_config.form_dashboard.input_caption.template')"
                        label-align-md="right"
                        label-class="pr-md-2"
                        :label-cols-md="3"
                    >
                        <v-single-select
                            :modelData="form.template_code"
                            :options="selectTemplate"
                            :placeholder="Trans.get('user_config.form_dashboard.input_caption.template')"
                            @onSelect="form.template_code = $event; onSelectTemplate($event);"
                            :state="!$v.form.template_code.$error ? null : false"
                            @change="$v.form.template_code.$touch()"
                        />
                        <invalid-tooltip
                            :inputItem="$v.form.template_code"
                            :fieldName="Trans.get('user_config.form_dashboard.input_caption.template_code')"
                        />
                    </b-form-group>

                    <!-- Tenant -->
                    <b-form-group
                        :label="Trans.get('user_config.form_dashboard.input_caption.tenant')"
                        label-align-md="right"
                        label-class="pr-md-2"
                        :label-cols-md="3"
                    >
                        <multiselect
                            v-model="form.tenant"
                            track-by="text"
                            label="text"
                            :placeholder="Trans.get('user_config.form_dashboard.input_caption.tenant')"
                            open-direction="bottom"
                            :options="selectKoperasi"
                            :searchable="true"
                            :internal-search="true"
                            :clear-on-select="true"
                            :close-on-select="true"
                            :options-limit="300"
                            :max-height="600"
                            :show-no-results="true"
                            :hide-selected="false"
                            :multiple="true"
                        >
                        </multiselect>
                    </b-form-group>

                    <!-- Select Type Config -->
                    <b-form-group
                        v-if="form.type"
                        :label="Trans.get('user_config.form_dashboard.input_caption.type')"
                        label-align-md="right"
                        label-class="pr-md-2"
                        :label-cols-md="3"
                    >
                        <b-input v-model="form.type" readonly />
                    </b-form-group>

                    <!-- Type Config (Jumlah) -->
                    <b-form-group
                        v-if="form.type_config"
                        :label="Trans.get('user_config.form_dashboard.input_caption.type_config')"
                        label-align-md="right"
                        label-class="pr-md-2"
                        :label-cols-md="3"
                    >
                        <b-input v-model="form.type_config" readonly />
                    </b-form-group>

                    <hr class="border-light" />

                    <b-form-group
                        label-align-md="right"
                        label-class="pr-md-3"
                        :label-cols-md="3"
                        class="mb-0"
                    >
                        <h5 class="font-weight-bold">{{ Trans.get("user_config.form_dashboard.label.feature") }}</h5>
                    </b-form-group>

                    <template v-if="form.tmpSelectedTemplate">

                        <div v-for="(v, k) in form.tmpSelectedTemplate.config.content" :key="k">
                            
                            <div v-if="v.content" class="border border-light mb-2 p-2">
                                <b-form-group
                                    label-align-md="right"
                                    label-class="pr-md-3"
                                    :label-cols-md="3"
                                    class="mt-2"
                                >
                                    <h6>
                                        <template v-if="v.type ==='content_row'">Content Row</template>
                                        <template v-else-if="v.type ==='content_column'">Content Column</template>
                                        <template v-else-if="v.type ==='column'">Column</template>
                                    </h6>
                                </b-form-group>

                                <div v-for="(v2, k2) in v.content" :key="k2">
                                    <b-form-group
                                        label-align-md="right"
                                        label-class="pr-md-3"
                                        :label-cols-md="3"
                                        class="mb-2"
                                    >
                                        <h6>
                                            <template v-if="v2.type ==='content_row'">Content Row {{ k2 + 1 }}</template>
                                            <template v-else-if="v2.type ==='content_column'">Content Column {{ k2 + 1 }}</template>
                                            <template v-else-if="v2.type ==='column'">Column {{ k2 + 1 }}</template>
                                        </h6>
                                        
                                        <template v-if="v2 && v2.content">
                                            <div v-for="(v3, k3) in v2.content" :key="k3">
                                                <b-input-group class="mt-3">
                                                    <b-input readonly :value="featureOptions.find(item => item.value === v3.feature) ? featureOptions.find(item => item.value === v3.feature).text : v3.feature" />
                                                    <b-input-group-append>
                                                        <b-button variant="danger" @click="removeFeature(k, k2, k3)">
                                                            <i class="fi fi-rs-trash"></i>
                                                        </b-button>
                                                    </b-input-group-append>
                                                </b-input-group>
                                            </div>
                                        </template>

                                        <div class="row">
                                            <div class="col-12">
                                                <div class="my-2 float-right">
                                                    <b-btn
                                                        variant="warning btn-sm w-icon w-100 w-auto-xl"
                                                        @click="showFormFeature(k, k2)"
                                                    >
                                                        <i class="fi fi-rs-add"></i>
                                                        <span>{{ Trans.get('user_config.form_dashboard.label.add_feature') }}</span>
                                                    </b-btn>
                                                </div>
                                            </div>
                                        </div>
                                    </b-form-group>
                                </div>
                            </div>

                            <div v-else class="border border-light mb-2 p-2">
                                <b-form-group
                                    label-align-md="right"
                                    label-class="pr-md-3"
                                    :label-cols-md="3"
                                    class="mb-2"
                                >
                                    <h6>
                                        <template v-if="v.type ==='content_row'">Content Row</template>
                                        <template v-else-if="v.type ==='content_column'">Content Column</template>
                                        <template v-else-if="v.type ==='column'">Column</template>
                                    </h6>

                                    <div v-if="v.type_config === 0">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mt-3 float-right">
                                                    <b-btn
                                                        variant="warning btn-sm w-icon w-100 w-auto-xl"
                                                        @click="showFormFeature(k, 0)"
                                                    >
                                                        <i class="fi fi-rs-add"></i>
                                                        <span>{{ Trans.get('user_config.form_dashboard.label.add_feature') }}</span>
                                                    </b-btn>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="my-2 float-right">
                                                    <b-btn
                                                        variant="warning btn-sm w-icon w-100 w-auto-xl"
                                                        @click="showFormFeature(k, 0)"
                                                    >
                                                        <i class="fi fi-rs-add"></i>
                                                        <span>{{ Trans.get('user_config.form_dashboard.label.add_feature') }}</span>
                                                    </b-btn>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </b-form-group>
                            </div>
                        </div>

                    </template>
                </b-card-body>

                <b-card-footer>
                    <b-row class="w-100">
                        <b-col md="3"></b-col>
                        <b-col>
                            <b-btn
                                variant="success btn-sm w-icon"
                                @click="formSubmitted"
                            >
                                <i class="fi fi-rs-disk"></i>
                                <span>{{ Trans.get("lang.save") }}</span>
                            </b-btn>
                        </b-col>
                    </b-row>
                </b-card-footer>
            </b-card>
        </b-container>

        <b-modal
            id="modal-feature"
            size="'lg'"
            centered
            no-fade
            ok-variant="sm btn-success"
            cancel-variant="sm btn-secondary"
            no-close-on-backdrop
        >
            <template #modal-title><h4>Feature</h4></template>
            <b-form-group class="mb-3">
                <v-single-select
                    :modelData="selectedFeature"
                    :options="featureOptions"
                    placeholder="Select feature"
                    @onSelect="onSelectFeature"
                />
            </b-form-group>
            <template #modal-footer>
                <b-button
                    variant="secondary"
                    class="btn-sm"
                    @click="$bvModal.hide('modal-feature')"
                >
                    <i class="fas fa-times"></i> 
                    Close
                </b-button>
                <b-button
                    variant="success"
                    class="btn-sm"
                    @click="saveTmpFeature"
                >
                    <i class="fas fa-save"></i> 
                    Save
                </b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
import { required, numeric } from "node_modules/vuelidate/lib/validators";
import Multiselect from "node_modules/vue-multiselect";

export default {
    name: "master-config-dashboard-form",
    components: {
        Multiselect,
    },
    validations() {
        return {
            form: {
                name: {
                    required,
                },
                type: {
                    required,
                },
                type_config: {
                    required,
                    numeric,
                },
                template_code: {
                    required,
                },
            },
        };
    },
    data: () => ({
        accessRuleKey: "moduser.config.dashboard",
        form: {
            name: "",
            description: "",

            type: "",
            type_config: null,

            features: [],
            tenant: [],
            template_code: "",

            tmpFeatures: [],

            tmpListFeature: [],
            tmpSelectedFeature: null,

            tmpSelectedFeatureIndex: {},

            config: {
                content: [],
            },

            tmpSelectedTemplate: {
                config: {
                    content: [],
                },
            },
        },

        selectKoperasi: [],
        selectTemplate: [],

        selectedFeature: [],
        tmpSelectedFeature: {},
        tmpListSelectedFeature: [],
    }),
    watch: {
        "form.template_code"(newCode) {
            if (newCode) {
                this.form.feature = [];
                this.$set(this.form, "tmpSelectedTemplate", this.listTemplate[newCode]);
                this.form.tmpSelectedFeatureIndex = {};
            }
        },
    },
    computed: {
        pageTitle() {
            return this.Trans.chose(this.Trans.get("user_config.form_dashboard.name"));
        },
        listFeature() {
            return this.$store.state.userConfig.templateList.feature;
        },
        featureOptions() {
            const featureOptions = [];
            _.forEach(this.listFeature, (val, i) => {
                _.forEach(Object.values(val.feature), (v, j) => {
                    featureOptions.push({
                        value: v.code,
                        text: v.name,
                    });
                });
            });
            return featureOptions;
        },
        listTemplate() {
            return this.$store.state.userConfig.templateList.template;
        },
        oneDashboard() {
            return this.$store.state.userConfig.dashboardForm;
        },
        isAdd() {
            return !this.$route.params.id;
        },
    },
    methods: {
        updateSelectedFeature(index1, index2, value) {
            this.tmpSelectedFeature = {
                ...this.tmpSelectedFeature,
                [index1]: {
                    ...this.tmpSelectedFeature[index1],
                    [index2]: value,
                },
            };
        },
        onSelectFeature(v) {
            this.form.tmpSelectedFeature = v;

            if (!this.tmpListSelectedFeature.includes(v))
                this.tmpListSelectedFeature.push(v);
        },
        showFormFeature(index1, index2) {
            this.form.tmpSelectedFeatureIndex = { index1, index2 };
            this.$bvModal.show("modal-feature");
        },
        saveTmpFeature() {
            const { index1, index2 } = this.form.tmpSelectedFeatureIndex;
            const content = this.form.tmpSelectedTemplate.config.content;

            if (!content[index1]) {
                this.$set(content, index1, { content: [] });
            }
            if (!content[index1].content) {
                this.$set(content[index1], 'content', []);
            }
            if (!content[index1].content[index2]) {
                this.$set(content[index1].content, index2, { content: [] });
            }
            if (!content[index1].content[index2].content) {
                this.$set(content[index1].content[index2], 'content', []);
            }

            this.$set(content[index1].content[index2].content, content[index1].content[index2].content.length, {
                feature: this.form.tmpSelectedFeature,
                config: []
            });

            this.$bvModal.hide("modal-feature");

            this.form.tmpSelectedFeatureIndex = null;
        },
        removeFeature(index1, index2, index3) {
            if (this.tmpListSelectedFeature.includes(this.form.tmpSelectedTemplate.config.content[index1].content[index2].content[index3].feature)) {
                this.tmpListSelectedFeature.splice(this.tmpListSelectedFeature.indexOf(this.form.tmpSelectedTemplate.config.content[index1].content[index2].content[index3].feature), 1);
            }

            this.form.tmpSelectedTemplate.config.content[index1].content[index2].content.splice(index3, 1);
        },

        loadListKoperasi() {
            this.Web.setLoadingPage(true);
            this.$store
                .dispatch("modcoopKoperasi/listKoperasi", { limit: 0 })
                .then((res) => {
                    this.selectKoperasi = [];
                    _.forEach(res.data, (val, i) => {
                        this.selectKoperasi.push({
                            text: "[" + val.tenant_id + "] - " + val.nama,
                            value: val.tenant_id,
                        });
                    });
                    if (!this.isAdd && this.oneDashboard.tenant) {
                        this.form.tenant = this.selectKoperasi.filter((item) =>
                            this.oneDashboard.tenant.includes(item.value)
                        );
                    }
                    this.Web.setLoadingPage(false);
                })
                .catch((res) => {
                    console.log("get koperasi/listKoperasi error : ", res);
                    this.Web.setLoadingPage(false);
                });
        },
        loadListTemplate() {
            this.$store
                .dispatch("userConfig/listTemplate")
                .then((res) => {
                    this.selectTemplate = [];
                    _.forEach(res.template, (val, i) => {
                        this.selectTemplate.push({
                            text: "[" + val.code + "] " + val.name,
                            value: val.code,
                        });
                    });
                })
                .catch((res) => {
                    console.log("get list template error : ", res);
                    this.Web.setLoadingPage(false);
                });
        },
        onSelectTemplate(e) {
            if (e) {
                this.form.tmpSelectedTemplate = null;
                _.forEach(this.listTemplate, (val, i) => {
                    if (val.code === e) {
                        this.form.type = val.config.type || "";
                        this.form.type_config = val.config.type_config || "";
                        this.form.tmpSelectedTemplate = val;
                    }
                });
            }
        },

        loadDashboard() {
            this.Web.setLoadingPage(true);
            this.$store
                .dispatch("userConfig/getDashboard", {
                    id: this.$route.params.id,
                })
                .then((res) => {
                    this.form = JSON.parse(JSON.stringify(res));
                    this.form.type = this.listTemplate[res.template_code].config.type || "";
                    this.form.type_config = this.listTemplate[res.template_code].config.type_config || "";

                    this.form.tmpSelectedTemplate = this.listTemplate[res.template_code];
                    this.form.tmpSelectedTemplate.config.content = res.content;

                    this.Web.setLoadingPage(false);
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text: this.Trans.get("alert.read_failed", {
                                attribute: this.Trans.get("ac.config"),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                    this.Web.setLoadingPage(false);
                });
        },

        formSubmitted(e) {
            e.preventDefault();
            this.$v.$touch();
            if (this.$v.$invalid) {
                this.Web.showAlert({
                    type: "danger",
                    title: this.Trans.get("alert.form_must_complete_title"),
                    text: this.Trans.get("alert.form_must_complete_text"),
                });
                return false;
            }

            if (this.isAdd) {
                this.saveData(this.form);
            } else {
                this.updateData(this.form);
            }
        },
        saveData(data) {
            const listTenantId = [];
            _.forEach(data.tenant, (val, i) => {
                listTenantId.push(val.value);
            });

            const formatedData = {
                name: data.name,
                description: data.description,
                type: data.type,
                type_config: data.type_config,
                feature: this.tmpListSelectedFeature,
                tenant: listTenantId,
                template_code: data.template_code,
                content: this.form.tmpSelectedTemplate.config.content
            };

            this.Web.setLoadingPage(true);
            this.$store
                .dispatch("userConfig/createDashboard", {
                    data: formatedData,
                })
                .then((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.success_title"),
                        text: this.Trans.get("alert.update_success", {
                            attribute: this.Trans.get("ac.config"),
                        }),
                        type: "success",
                    });

                    this.Web.setLoadingPage(false);
                    this.$router.push({
                        name: "moduser.config.dashboard",
                    });
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text: this.Trans.get("alert.update_failed", {
                                attribute: this.Trans.get("ac.config"),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                    this.Web.setLoadingPage(false);
                });
        },
        updateData(data) {
            const listTenantId = [];
            _.forEach(data.tenant, (val, i) => {
                listTenantId.push(val.value);
            });

            var listFeature = [];
            if (!this.tmpListSelectedFeature.length) {
                listFeature = this.form.feature;
            } else {
                listFeature = this.tmpListSelectedFeature;
            }

            const formatedData = {
                name: data.name,
                description: data.description,
                type: data.type,
                type_config: data.type_config,
                feature: listFeature,
                tenant: listTenantId,
                template_code: data.template_code,
                content: this.form.tmpSelectedTemplate.config.content
            };

            this.Web.setLoadingPage(true);
            this.$store
                .dispatch("userConfig/updateDashboard", {
                    data: formatedData,
                    id: this.$route.params.id,
                })
                .then((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.success_title"),
                        text: this.Trans.get("alert.update_success", {
                            attribute: this.Trans.get("ac.config"),
                        }),
                        type: "success",
                    });

                    this.Web.setLoadingPage(false);
                    this.$router.push({
                        name: "moduser.config.dashboard",
                    });
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text: this.Trans.get("alert.update_failed", {
                                attribute: this.Trans.get("ac.config"),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                    this.Web.setLoadingPage(false);
                });
        },

        initView() {
            this.Web.setModule("moduser");

            this.Web.setNavbarTitle(
                this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption)
            );

            this.Web.resetBreadcrumb();
            
            this.Web.addBreadcrumb(
                this.Trans.get("lang.home"), 
                { name: "home",}
            );
            this.Web.addBreadcrumb(
                this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption),
                { name: "user.list" }
            );
            this.Web.addBreadcrumb(
                this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.config.children.dashboard.caption),
                { name: "moduser.config.dashboard" }
            );
            this.Web.addBreadcrumb(this.pageTitle);

            this.Web.setBodyWithPadding(false);
            this.Web.setShow("moduser");
        },
    },
    created() {
        if (!this.UserAuth.hasAccess(this.accessRuleKey)) {
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

        this.loadListKoperasi();
        this.loadListTemplate();

        if (!this.isAdd) this.loadDashboard();
    },
};
</script>
