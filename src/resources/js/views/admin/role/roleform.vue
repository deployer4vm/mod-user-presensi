<template>
    <div>
        <header-breadcrumb :pageTitle="pageTitle" :backPath="{ name: 'role.list' }" />
        <b-container>
            <b-card class="my-3" no-body>
                <b-card-body class="pb-0">
    
                    <!-- Role Name -->
                    <b-form-group :label="Trans.get('role.field_caption.name')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-input :disabled="isDisabled || !canEditData" :state="$v.data.formData.form.name.$error ? false : null" @change="$v.data.formData.form.name.$touch()" v-model="data.formData.form.name" @blur="formatRoleCodeFromRoleName" />
                        <invalid-tooltip :inputItem="$v.data.formData.form.name" :fieldName="Trans.get('role.field_caption.name')" />
                    </b-form-group>
    
                    <!-- Role Code -->
                    <b-form-group :label="Trans.get('role.field_caption.role_code')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-input :state="$v.data.formData.form.role_code.$error ? false : null" @change="$v.data.formData.form.role_code.$touch()" v-model="data.formData.form.role_code" @blur="formatRoleCode" :disabled="!(isAdd || (!isDisabled && canEditRoleCode))" />
                        <invalid-tooltip :inputItem="$v.data.formData.form.role_code" :fieldName="Trans.get('role.field_caption.role_code')" />
                    </b-form-group>
    
                    <!-- <b-form-group v-if="AppConfig.system.multitenant.active" :label="Trans.get('role.field_caption.tenant_group')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select v-model="data.formData.form.tenant_group_id" :options="tenantGroupOption" />
                    </b-form-group> -->
                    
                    <!-- Level -->
                    <b-form-group :label="Trans.get('role.field_caption.level')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select :state="$v.data.formData.form.level.$error ? false : null" @change="$v.data.formData.form.level.$touch()" v-model="data.formData.form.level" :options="levelOption" :disabled="!(isAdd || (!isDisabled &&canEditLevel))" class="form-control"/>
                        <invalid-tooltip :inputItem="$v.data.formData.form.level" :fieldName="Trans.get('role.field_caption.level')" />
                    </b-form-group>
    
                    <!-- Role Group -->
                    <b-form-group :label="Trans.get('role.field_caption.role_group_id')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select :disabled="isDisabled || !canEditRoleGroupId" v-model="data.formData.form.role_group_id" :options="selectRoleGroup" class="form-control"/>
                    </b-form-group>
    
                    <!-- Role Type -->
                    <b-form-group :label="Trans.get('role.field_caption.role_type')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select :disabled="isDisabled || !canEditRoleType" v-model="data.formData.form.role_type" :options="selectRoleType" class="form-control"/>
                    </b-form-group>
    
                    <!--  -->

                    <!-- Select Dashboard -->
                    <b-form-group :label="Trans.get('role.field_caption.dashboard_type')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select :disabled="isDisabled || !canEditDashboard" v-model="data.formData.form.dashboard_type" :options="selectDashboard" class="form-control"/>
                    </b-form-group>
                    
                    <!-- bypass_dashboard -->
                    <b-form-group 
                        :label="Trans.get('role.field_caption.bypass_dashboard')" 
                        label-align-md="right" 
                        label-class="pr-md-5" 
                        :label-cols-md="5"
                        v-if="canEditGlobalData && data.formData.form.is_global"
                    >
                        <b-select 
                            v-model="data.formData.form.bypass_dashboard" 
                            :options="selectBypass" 
                            class="form-control"
                        />
                    </b-form-group>
    
                    <!-- Select Datarule isDataruleEnabled -->
                    <template v-if="false">
                        <b-form-group :label="Trans.get('role.field_caption.datarule_id')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                            <b-select :disabled="isDisabled || !canEditDatarule" v-model="data.formData.form.datarule_id" :options="selectDatarule" class="form-control"/>
                        </b-form-group>       
                        <!-- TO DO : khusus edit, pilihan replace semua datarule user custom di user role atau tidak -->
                        <!-- <b-form-group 
                            :label="Trans.get('role.field_caption.bypass_datarule')" 
                            label-align-md="right" 
                            label-class="pr-md-5" 
                            :label-cols-md="5"
                            v-if="canEditGlobalData && data.formData.form.is_global"
                        >
                            <b-select 
                                v-model="data.formData.form.bypass_datarule" 
                                :options="selectBypass" 
                                class="form-control"
                            />
                        </b-form-group>             -->

                        <!-- GLOBAL bypass_datarule -->
                        <b-form-group 
                            :label="Trans.get('role.field_caption.bypass_datarule')" 
                            label-align-md="right" 
                            label-class="pr-md-5" 
                            :label-cols-md="5"
                            v-if="canEditGlobalData && data.formData.form.is_global"
                        >
                            <b-select 
                                v-model="data.formData.form.bypass_datarule" 
                                :options="selectBypass" 
                                class="form-control"
                            />
                        </b-form-group>
                    </template>

                    <!--  -->

                    <!-- Locked Data -->
                    <b-form-group v-if="canEditLockedDataMode" :label="Trans.get('role.field_caption.locked_data_mode')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select :disabled="!canEditData" v-model="data.formData.form.locked_data_mode" :options="selectLockedDataMode" class="form-control"/>
                    </b-form-group>

                    <!-- GLOBA CONFIG -->
                    <div class="border rounded mx-2 my-3 pt-3 px-3" v-if="canEditGlobalData">             

                        <!-- is_global -->
                        <b-form-group
                            :label="Trans.get('role.field_caption.is_global')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3"
                        >
                            <b-select v-model="data.formData.form.is_global" :options="selectIsGlobal" class="form-control"/>
                        </b-form-group>

                        <hr>

                        <!-- global edit config -->
                        <b-tabs class="nav-tabs-top mb-4" v-if="data.formData.form.is_global">
                            <template v-for="(v,i) in data.formData.form.global">
                                <b-tab :title="i?(v.tenant_id&&listTenant[v.tenant_id]?('[' + v.tenant_id + '] ' + listTenant[v.tenant_id].group_app):'...'):'Global'" :key="'sdfsd-' + i" active>
                                    <div class="card-body">  

                                        <div class="text-right pb-2">
                                            <b-button v-if="i!=0" size="sm" variant="danger" @click="closeGlobalTab(i)">
                                                Delete
                                            </b-button>
                                        </div>

                                        <div>
                                            <!-- tenant_id -->
                                            <b-form-group
                                                :label="Trans.get('role.field_caption.tenant_id')"
                                                label-align-md="right"
                                                label-class="pr-md-3"
                                                :label-cols-md="3"
                                                v-if="i!=0"
                                            >
                                                <v-single-select
                                                    :modelData="data.formData.form.global[i].tenant_id"
                                                    @onSelect="data.formData.form.global[i].tenant_id = $event"
                                                    :options="selectTenant"
                                                    :placeholder="Trans.get('lang.select')"
                                                />
                                            </b-form-group>

                                            <!-- global_bypass_rule -->
                                            <b-form-group 
                                                :label="Trans.get('role.field_caption.global_bypass_rule')" 
                                                label-align-md="right" 
                                                label-class="pr-md-3" 
                                                :label-cols-md="3"
                                            >
                                                <b-select 
                                                    v-model="data.formData.form.global[i].global_bypass_rule" 
                                                    :options="selectGlobalBypass" 
                                                    class="form-control"
                                                />
                                            </b-form-group>
                                            
                                            <!-- global_bypass_datarule -->
                                            <b-form-group 
                                                :label="Trans.get('role.field_caption.global_bypass_datarule')" 
                                                label-align-md="right" 
                                                label-class="pr-md-3" 
                                                :label-cols-md="3"
                                            >
                                                <b-select v-model="data.formData.form.global[i].global_bypass_datarule" :options="selectGlobalBypass" class="form-control"/>
                                            </b-form-group>
                                            
                                            <!-- global_bypass_dashboard -->
                                            <b-form-group 
                                                :label="Trans.get('role.field_caption.global_bypass_dashboard')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3"
                                            >
                                                <b-select v-model="data.formData.form.global[i].global_bypass_dashboard" :options="selectGlobalBypass" class="form-control"/>
                                            </b-form-group>
                                            
                                            <!-- global_bypass_notification -->
                                            <b-form-group 
                                                :label="Trans.get('role.field_caption.global_bypass_notification')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3"
                                            >
                                                <b-select v-model="data.formData.form.global[i].global_bypass_notification" :options="selectGlobalBypass" class="form-control"/>
                                            </b-form-group>
                                        </div>
                                    </div>
                                </b-tab>
                            </template>
                            <template #tabs-end>
                                <b-nav-item role="presentation" @click.prevent="newGlobalTab" href="#"><b>+</b></b-nav-item>
                            </template>
                        </b-tabs>
                    </div>   
    
                </b-card-body>
            </b-card>            
                    
            <div class="my-3 row" v-if="data.formData.form.role_type==1">
                <div class="col">                    
                    <h5>
                        Rule
                    </h5>
                </div>
                <div class="col">
                    <!-- bypass_rule -->
                    <b-form-group 
                        :label="Trans.get('role.field_caption.bypass_rule')" 
                        label-align-md="right" 
                        label-class="pr-md-5" 
                        :label-cols-md="5"
                        v-if="canEditGlobalData && data.formData.form.is_global"
                    >
                        <b-select 
                            v-model="data.formData.form.bypass_rule" 
                            :options="selectBypass" 
                            class="form-control"
                        />
                    </b-form-group>
                </div>   
            </div>

            <roleform-rule
                :roleLoaded="triggerRuleLoad"
                :ruleData="ruleData"
                :roleSaved="triggerRuleSave"
                @onRuleReady="save()"
            />
            <!-- v-if="pageLoaded" -->
        
            <div class="text-right mt-3">
                <b-btn variant="primary w-icon" @click="prepareSave">
                    <i class="fi fi-rs-disk"></i>
                    <span>{{ Trans.get("lang.save_change") }}</span>
                </b-btn>
            </div>
        </b-container>
    </div>
</template>
<!-- Page -->
<style src="@/vendor/styles/pages/users.scss" lang="scss"></style>

<script>
import { required, minLength, minValue } from "node_modules/vuelidate/lib/validators";
import roleformRule from "./component/roleformRule";

export default {
    name: "pages-role-form",
    components: {
        roleformRule
    },
    metaInfo() {
        return { title: this.pageTitle };
    },
    data: () => ({
        pageLoaded: false,
        curTab:'',
        accessRuleKey: "moduser.role",
        permissions: [], //data acl
        // form
        listRoleGroup: [],//list rolegroup dengan key "id"
        listDatarule: [],//list rolegroup dengan key "id"
        listDashboard: [],
        listTenant: [],
        //
        selectRoleGroup: [],
        selectLockedDataMode: [
            {text:'Public',value:0},
            {text:'Tidak bisa didelete',value:1},
            {text:'Tidak bisa diedit dan didelete',value:2},
        ],
        selectRoleType: [
            {text:'Standard',value:1},
            {text:'Non-Login role',value:2},
        ],
        selectIsGlobal: [
            {text:'Tenant Manager Only',value:0},
            {text:'Global',value:1},
        ],
        selectGlobalBypass: [
            {text:'Diedit dari Tenant Manager saja',value:0},
            {text:'Bisa diedit dari tenant',value:1},
            {text:'Bisa diedit dari tenant (khusus user dengan hak akses)',value:2}, // moduser.role.can_bypass_global
        ],         
        selectBypass: [
            {text:'Tidak di-replace',value:0},
            {text:'Di-replace',value:1},
        ],
        selectDatarule: [
            {text:'Default Datarule',value:0},
        ],        
        selectDashboard: [
            {text:'Default Dashboard',value:0},
        ],       
        selectTenant: [],
        //
        disabledModuleAccess: {},
        rulePerModule: {},
        levelOption: [],
        tenantOption: [],
        // tenantGroupOption: [],
        ruleData:null,
        triggerRuleLoad:null,
        triggerRuleSave:null
    }),
    validations() {
        return {
            data:{
                formData: {
                    form : {
                        name: {
                            required,
                            minLength: minLength(5),
                        },
                        role_code: {
                            required,
                            minLength: minLength(5),
                        },
                        level: {
                            required,
                        },
                    },
                }
            }            
        };
    },
    computed: { 
        data: {
            get() {
                return this.$store.state.moduserView.dataRole;
            },
            set(value) {
                this.$store.commit("moduserView/setDataRole", value);
            }
        },
        isAdd() {
            return this.$route.params.roleId ? false : true;
        },
        pageTitle() {
            return this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.role.caption);
        },
        title() {
            return this.isAdd ? this.Trans.get("role.roleform.form_add_caption") : ("#" + this.data.formData.form.name);
        },
        // tenantGroup() {
        //     return this.$store.state.tenant.listTenantGroup;
        // },        
        tenantGroup() {
            return this.$store.getters.getTenantGroup;
        },
        isDataruleEnabled() {
            return this.AppConfig.packageLocal.moduser.datarule.enable?true:false;
        },
        //
        isDisabled() {
            return this.data.formData.form.locked_data_mode == 2;
        },
        // untuk detek global data bukan
        canEditData() {
            return this.data.formData.form.is_global == 0 || isOnTenantManager;
        },
        //
        canEditRoleCode() {
            return this.canEditData && (this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_code') || this.UserAuth.isWebdev());
        },
        canEditRoleType() {
            return this.canEditData && (this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_type') || this.UserAuth.isWebdev());
        },        
        canEditRoleGroupId() {
            return this.canEditData && (this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_group_id') || this.UserAuth.isWebdev());
        },
        canEditLevel() {
            return this.canEditData && (this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_level') || this.UserAuth.isWebdev());
        },
        canEditRule() {
            return this.canEditData && (this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_rule') || this.UserAuth.isWebdev());
        },
        // apakah bisa edit data-data role global (khusus di tenant manager)
        canEditGlobalData() {
            return isOnTenantManager && (this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_global_data') || this.UserAuth.isWebdev());
        },
        // khusus di tenant - apakah punya akses untuk edit/manage global role 
        canBypassGlobal() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_bypass_global') || this.UserAuth.isWebdev();
        },
        //
        canEditRuleGlobal() {
            return this.canEditRule && (this.canEditData || (this.data.formData.form.global_bypass_rule==1 || (this.data.formData.form.global_bypass_rule==2 && this.canBypassGlobal)));
        },
        canEditDashboard() {
            return this.canEditData || (this.data.formData.form.global_bypass_dashboard==1 || (this.data.formData.form.global_bypass_dashboard==2 && this.canBypassGlobal));
        },
        canEditDatarule() {
            return this.canEditData || (this.data.formData.form.global_bypass_datarule==1 || (this.data.formData.form.global_bypass_datarule==2 && this.canBypassGlobal));
        },
        //
        canEditLockedDataMode() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_locked_data_mode') || this.UserAuth.isWebdev();
        },
    },
    methods: {
        //load data role yang akan diedit
        loadRole() {
            let params = {};
            if(!this.canEditRoleType)
                params.role_type = 1;
            
            params['append[0]'] = 'global';
            this.Web.setLoadingPage(true);
            this.$store
                .dispatch("role/getRole", {
                    id: this.$route.params.roleId,
                    params:params
                })
                .then((res) => {
                    this.checkSystemRole(res);

                    //copy semua data role ke roleform nya kecuali field rule, karena field rule akan di-assign
                    //selanjutnya sesuai format yang digunakan di roleform ini
                    _.forEach(res, (v, k) => {
                        if (k != "rule") this.data.formData.form[k] = v;
                    });

                    //init rule di component rule
                    this.pageLoaded = true;
                    this.ruleData = res.rule;  
                    this.triggerRuleLoad = true;     

                    this.initView();
                    this.Web.setLoadingPage(false);
                })
                .catch((res) => {
                    this.Web.setLoadingPage(false);
                    this.Web.showAlert({ text: "Role not found", style: "warning" });
                    this.$router.push({ name: "role.list" });
                });
        },
        loadRoleGroup() {         
            this.Repo('moduserRoleGroup')
                .readList({saveState:false})
                .then((res) => {
                    this.selectRoleGroup = [{text: '-- Ungrouped --', value: 0}];
                    if (res.count == 0) {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.info_title"),
                            text: this.Trans.get("lang.no_data"),
                            type: "info",
                        });
                    }else{
                        res.data.forEach((v,i) => {
                            this.listRoleGroup[v.id] = v;
                            this.selectRoleGroup.push({
                                value: v.id,
                                text: v.name + (v.is_global?' [global]':''),
                            });
                        });
                    }
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.read_failed", {
                                attribute: this.Trans.get('role.role_group.name'),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },
        loadDatarule() {         
            this.Repo('moduserRoleDatarule')
                .readList({saveState:false})
                .then((res) => {
                    this.selectDatarule = [{text: 'Default Datarule', value: 0}];
                    if (res.count == 0) {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.info_title"),
                            text: this.Trans.get("lang.no_data"),
                            type: "info",
                        });
                    }else{
                        res.data.forEach((v,i) => {
                            this.listDatarule[v.id] = v;
                            this.selectDatarule.push({
                                value: v.id,
                                text: v.name + (v.is_global?' [global]':''),
                            });
                        });
                    }
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.read_failed", {
                                attribute: this.Trans.get('role.datarule.data_datarule.name'),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },        
        loadDashboard() {         
            // this.$store.dispatch('userConfig/ssdfsd',{
            //         saveState:false
            //     })                
            this.Repo('moduserConfigDashboard')
                .readList({saveState:false})
                .then((res) => {
                    this.selectDashboard = [{text: 'Default Dashboard', value: 0}];
                    if (res.count == 0) {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.info_title"),
                            text: this.Trans.get("lang.no_data"),
                            type: "info",
                        });
                    }else{                        
                        res.data.forEach((v,i) => {
                            this.listDashboard[v.id] = v;
                            this.selectDashboard.push({
                                value: v.id,
                                text: v.name,
                            });
                        });
                    }
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.read_failed", {
                                attribute: 'Dashboar'//this.Trans.get('role.datarule.data_datarule.name'),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },
        loadTenant() { 
            // this.Repo('moduserRoleDatarule')
            //     .readList({saveState:false})
            this.$store.dispatch('userConfig/listTenant',{
                    saveState:false
                })          
                .then((res) => {
                    if (res.count == 0) {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.info_title"),
                            text: this.Trans.get("lang.no_data"),
                            type: "info",
                        });
                    }else{
                        this.selectTenant = [];// [{text: 'Default Datarule', value: 0},]
                        res.data.forEach((v,i) => {
                            this.listTenant[v.id] = v;
                            this.selectTenant.push({
                                value: v.id,
                                text: '[' + v.id + ' - ' + v.group_app + '] ' + v.name + ' (' + v.apps_mode + ')',
                            });
                        });
                    }
                })
                .catch((res) => {
                    this.Web.showAlert({
                        title: this.Trans.get("alert.warning_title"),
                        text:
                            this.Trans.get("alert.read_failed", {
                                attribute: 'Tenant'//this.Trans.get('role.datarule.data_datarule.name'),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
        },
        //format nama role agar sesuai aturan penamaan role nya
        formatRoleCodeFromRoleName(v) {
            this.$v.data.formData.form.name.$touch();
            if (this.data.formData.form.role_code == "") {
                this.data.formData.form.role_code = this.data.formData.form.name.toLowerCase().replace(/[^a-zA-Z0-9_]/g, "_");
            }
        },
        //format penamaan role_code agar sesuai aturan penamaan role_code nya
        formatRoleCode(v) {
            this.$v.data.formData.form.role_code.$touch();
            this.data.formData.form.role_code = this.data.formData.form.role_code.replace(/[^a-zA-Z0-9_]/g, "_");
        },
        prepareSave() {
            // this.data.formData.form.role_group_code = this.listRoleGroup[this.data.formData.form.role_group_id]?this.listRoleGroup[this.data.formData.form.role_group_id].code:'';
            // this.data.formData.form.datarule_code = this.listDatarule[this.data.formData.form.datarule_id]?this.listDatarule[this.data.formData.form.datarule_id].code:'';
            
            // trigger rule save di component rule
            this.triggerRuleSave = true;
        },        
        save() {
            this.$v.$touch();
            if (this.$v.$invalid) {
                this.Web.showAlert({
                    type: "danger",
                    title: this.Trans.get("alert.form_must_complete_title"),
                    text: this.Trans.get("alert.form_must_complete_text"),
                });
            } else {
                if (this.data.formData.isAdd) {
                    
                    if (!this.UserAuth.hasAccess(this.accessRuleKey, "c")) {
                        //goto dashboard current tenant
                        this.Web.goToCurrentTenant();
                        this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), style: "warning" });
                        return false;
                    }
                    this.$store
                        .dispatch("role/create", this.data.formData.form)
                        .then((res) => {
                            this.Web.showAlert({ type: "info", text: "Role Registered Successfully" });
                            this.$router.push({ name: "role.list" });
                        })
                        .catch((err) => {
                            console.log("create role error : ", err);
                            this.Web.showAlert({ type: "danger", text: "Save data failed" });
                        });
                } else {
                    if (!this.UserAuth.hasAccess(this.accessRuleKey, "u")) {
                        //goto dashboard current tenant
                        this.Web.goToCurrentTenant();
                        this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), style: "warning" });
                        return false;
                    }

                    this.$store
                        .dispatch("role/update", { data: this.data.formData.form, id: this.data.formData.form.id })
                        .then((res) => {
                            this.Web.showAlert({ type: "info", text: "Role Updated Successfully" });
                            this.$router.push({ name: "role.list" });
                        })
                        .catch((err) => {
                            console.log("update role error : ", err);
                            this.Web.showAlert({ type: "danger", text: "Save data failed" });
                        });
                }
            }
        },
        checkSystemRole(data) {
            if (data.system_role !== 0) {
                this.$router.push({ name: "role.list" });
                this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), type: "warning" });
            }
        },
        newGlobalTab() {
            this.data.formData.form.global.push(JSON.parse(JSON.stringify(this.data.formData.formGlobalEmpty)));
        },
        closeGlobalTab(idx) {
            this.data.formData.form.global.splice(idx, 1);
            // for (let i = 0; i < this.tabs.length; i++) {
            //     if (this.tabs[i] === x) {
            //         this.tabs.splice(i, 1);
            //     }
            // }
        },
        initView() {
            this.Web.setModule("moduser");

            this.Web.setNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
            // this.Web.appendNavbarTitle(this.Trans.chose(this.AppConfig.packageLocal.PSBBI.access.children.module.caption));

            this.Web.resetBreadcrumb();
            this.Web.addBreadcrumb(this.Trans.get("lang.home"));
            this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.caption));
            this.Web.addBreadcrumb(this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.role.caption), { name: "role.list" });
            this.Web.addBreadcrumb(this.title);

            this.Web.setBodyWithPadding(false);
            this.Web.setShow("moduser");
        },
    },
    created() {
        if (!this.UserAuth.hasAccess(this.accessRuleKey)) {
            //goto dashboard current tenant
            // this.Web.goToCurrentTenant();
            this.$router.push({ name: "role.list" });
            this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), style: "warning" });
            return false;
        }
        this.loadRoleGroup();
        this.loadDashboard();
        
        if(isOnTenantManager)
            this.loadTenant();

        if(this.isDataruleEnabled)
            this.loadDatarule();

        if (this.isAdd) {
            this.data.formData.isAdd = true;
            this.pageLoaded = true;
            this.data.formData.form = JSON.parse(JSON.stringify(this.data.formData.formEmpty));
            this.initView();
        }else{
            this.data.formData.isAdd = false;
            this.loadRole();
        }

        // -----
        var startI = parseInt(this.UserAuth.getUser('level')) + 1;

        var endI = parseInt(this.AppConfig.packageLocal.moduser.user_role.user_level.max) + 1;
        if (startI < parseInt(this.AppConfig.packageLocal.moduser.user_role.user_level.min)) startI = parseInt(this.AppConfig.packageLocal.moduser.user_role.user_level.max);

        //load level
        for (var i = startI; i < endI; i++) {
            this.levelOption[i] = i;
        }
        // -----

        this.Web.setBodyWithPadding(false);
    },
    // destroyed() {
    //     this.Web.setBodyWithPadding(true);
    // }
};
</script>
