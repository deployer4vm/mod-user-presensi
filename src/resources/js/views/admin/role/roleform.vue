<template>
    <div>
        <header-breadcrumb :pageTitle="pageTitle" :backPath="{ name: 'role.list' }" />
        <b-container>
            <b-card class="my-3" no-body>
                <b-card-body class="pb-0">
    
                    <!-- Role Name -->
                    <b-form-group :label="Trans.get('role.field_caption.name')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-input :disabled="isDisabled" :state="$v.roleForm.name.$error ? false : null" @change="$v.roleForm.name.$touch()" v-model="roleForm.name" @blur="formatRoleCodeFromRoleName" />
                        <invalid-tooltip :inputItem="$v.roleForm.name" :fieldName="Trans.get('role.field_caption.name')" />
                    </b-form-group>
    
                    <!-- Role Code -->
                    <b-form-group :label="Trans.get('role.field_caption.role_code')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-input :state="$v.roleForm.role_code.$error ? false : null" @change="$v.roleForm.role_code.$touch()" v-model="roleForm.role_code" @blur="formatRoleCode" :disabled="!(isAdd || (!isDisabled && canEditRoleCode))" />
                        <invalid-tooltip :inputItem="$v.roleForm.role_code" :fieldName="Trans.get('role.field_caption.role_code')" />
                    </b-form-group>
    
                    <!-- <b-form-group v-if="AppConfig.system.multitenant.active" :label="Trans.get('role.field_caption.tenant_group')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select v-model="roleForm.tenant_group_id" :options="tenantGroupOption" />
                    </b-form-group> -->
                    
                    <!-- Level -->
                    <b-form-group :label="Trans.get('role.field_caption.level')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select :state="$v.roleForm.level.$error ? false : null" @change="$v.roleForm.level.$touch()" v-model="roleForm.level" :options="levelOption" :disabled="!(isAdd || (!isDisabled &&canEditLevel))" class="form-control"/>
                        <invalid-tooltip :inputItem="$v.roleForm.level" :fieldName="Trans.get('role.field_caption.level')" />
                    </b-form-group>
    
                    <!--  -->
    
                    <!-- Role Group -->
                    <b-form-group :label="Trans.get('role.field_caption.role_group_id')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select :disabled="isDisabled || !canEditRoleGroupId" v-model="roleForm.role_group_id" :options="selectRoleGroup" class="form-control"/>
                    </b-form-group>
    
                    <!-- Role Type -->
                    <b-form-group v-if="canEditRoleType" :label="Trans.get('role.field_caption.role_type')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select v-model="roleForm.role_type" :options="selectRoleType" class="form-control"/>
                    </b-form-group>
    
                    <!-- Locked Data -->
                    <b-form-group v-if="canEditLockedDataMode" :label="Trans.get('role.field_caption.locked_data_mode')" label-align-md="right" label-class="pr-md-3" :label-cols-md="3">
                        <b-select v-model="roleForm.locked_data_mode" :options="selectLockedDataMode" class="form-control"/>
                    </b-form-group>
    
                </b-card-body>
            </b-card>
    
            <!-- input Rule -->
            <div class="my-3" v-if="roleForm.role_type==1 && canEditRoleRule && pageLoaded">
                <div class="row">
                    <!-- Looping nama module di sidebar-->
                    <div class="col-md-3 pt-0">
                        <b-list-group class="account-settings-links" flush>
                            <!-- <b-list-group-item class="text-right">
                                <h4 class="font-weight-normal mb-0">{{ Trans.get("role.roleform.module") }}</h4>
                            </b-list-group-item> -->
                            <b-list-group-item button 
                                :active="curTab == key || (curTab == '' && i==0)" 
                                @click="curTab = key" 
                                v-for="(moduleRule, key, i) in AppConfig.acl" 
                                :key="'role-judul-' + i"
                            >
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>{{ i + 1 }}. {{Trans.chose(moduleRule.acl_caption)}} </span>
                                    <span v-if="roleForm.rule[key].has_access" class="d-flex align-items-center justify-content-center rounded-circle overflow-hiddens p-1 bg-success" style="width: 20px; height: 20px;">
                                        <i class="text-white fi fi-rs-check-circle"></i>
                                    </span>
                                </div>
                            </b-list-group-item>
                        </b-list-group>
                    </div>                    
                    
                    <!-- Looping Modules -->
                    <template v-for="(moduleRule, key, i) in AppConfig.acl">
                        <template v-if="isInGroup(moduleRule.tenant_group_id)">
                            <div :key="'role-detail-' + i" v-show="curTab == key || (curTab == '' && i==0)" class="col-md-9"> 
                                <b-card no-body class="overflow-hidden position-relative mb-0 ml-md-3 ml-md-3">
                                    <!-- header -->
                                    <b-card-header>
                                        <div class="d-flex align-items-center justify-content-between w-100">  
                                            <!--  -->
                                            <div>
                                                <!-- nama fitur -->
                                                <b-check class="my-1" :disabled="isDisabled" :value="1" :unchecked-value="0" v-model="roleForm.rule[key].has_access" @change="setModule(key, $event)">
                                                    <h5 class="font-weight-normal m-0">{{ i + 1 }}. {{ Trans.get("role.roleform.rule") }} <b>{{Trans.chose(moduleRule.acl_caption)}}</b></h5>
                                                    <!-- {{ Trans.get("role.roleform.has_access") }} -->
                                                </b-check>
                                                <div v-if="moduleRule.acl_description != ''">
                                                    <i>{{ Trans.chose(moduleRule.acl_description) }}</i>
                                                </div>
                                            </div>
                                            <!-- tombol check / uncheck se-module -->
                                            <div>
                                                <b-btn variant="dark w-icon" size="xs" :disabled="disabledModuleAccess[key]" @click="checkModuleAll(key)">
                                                    <i class="fi fi-rs-check-double"></i>
                                                    <span>{{ Trans.get("role.roleform.check_all") }}</span>
                                                </b-btn>
                                                <b-btn variant="dark w-icon" size="xs" :disabled="disabledModuleAccess[key]" @click="uncheckModuleAll(key)">
                                                    <i class="fi fi-rs-trash-can-check"></i>
                                                    <span>{{ Trans.get("role.roleform.uncheck_all") }}</span>
                                                </b-btn>
                                            </div>
                                        </div>
                                    </b-card-header>
                                    <!-- list rule -->
                                    <b-card-body>
                                        <div class="table-responsive mb-0" >
                                            <table class="table mb-0 table-hover not-responsive">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ Trans.get("role.roleform.feature") }}</th>
                                                        <th>{{ Trans.get("role.roleform.create") }}</th>
                                                        <th>{{ Trans.get("role.roleform.read") }}</th>
                                                        <th>{{ Trans.get("role.roleform.update") }}</th>
                                                        <th>{{ Trans.get("role.roleform.delete") }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Looping Rule -->
                                                    <template v-for="(rule, ruleKey, index) in moduleRule.children">
                                                        <tr :key="'rulekey' + ruleKey" v-if="isInGroup(rule.tenant_group_id) && ((UserAuth.hasAccess(ruleKey, 'has_access') && calcPadding(ruleKey) == 0) || UserAuth.hasAccess(ruleKey, 'c') || UserAuth.hasAccess(ruleKey, 'r') || UserAuth.hasAccess(ruleKey, 'u') || UserAuth.hasAccess(ruleKey, 'd'))">
                                                            <th scope="row">{{ i + 1 }}.{{ index + 1 }}</th>
                                                            <td>
                                                                <div :style="'padding-left: ' + calcPadding(ruleKey) + 'px;'">
                                                                    <!-- <span class="ion ion-md-return-right" v-if="calcPadding(ruleKey)>1"></span> -->
                                                                    {{ Trans.chose(rule.acl_caption) }}
                                                                    <div v-if="rule.acl_description != ''">
                                                                        <i>{{ Trans.chose(rule.acl_description) }}</i>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <!-- jika hanya has_access nya aja yg ditampilkan, maka cukup tampilkan 1 check rule -->
                                                            <template v-if="rule.crud.c != 1 && rule.crud.r != 1 && rule.crud.u != 1 && rule.crud.d != 1">
                                                                <td colspan="4">
                                                                    <b-check :value="1" :unchecked-value="0" v-model="roleForm.rule[ruleKey].has_access" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(roleForm.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'has_access')">
                                                                        {{ Trans.get("role.roleform.has_access") }}
                                                                    </b-check>
                                                                </td>
                                                            </template>
                                                            <template v-else
                                                                ><!-- jika menampilkan check CRUD -->
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="roleForm.rule[ruleKey].c" v-if="rule.crud.c == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(roleForm.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'c')" class="px-2 m-0" />
                                                                </td>
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="roleForm.rule[ruleKey].r" v-if="rule.crud.r == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(roleForm.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'r')" class="px-2 m-0" />
                                                                </td>
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="roleForm.rule[ruleKey].u" v-if="rule.crud.u == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(roleForm.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'u')" class="px-2 m-0" />
                                                                </td>
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="roleForm.rule[ruleKey].d" v-if="rule.crud.d == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(roleForm.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'd')" class="px-2 m-0" />
                                                                </td>
                                                            </template>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </b-card-body>
                                </b-card>
                            </div>
                        </template>
                    </template>
                </div>
            </div>
    
            <div class="text-right mt-3">
                <b-btn variant="primary w-icon" @click="save">
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

export default {
    name: "pages-role-form",
    metaInfo() {
        return { title: this.pageTitle };
    },
    data: () => ({
        pageLoaded: false,
        curTab:'',
        accessRuleKey: "moduser.role",
        permissions: [], //data acl
        // form
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
        
        roleForm: {
            id: 0,
            name: "",
            role_code: "",
            level: 2,
            // tenant_group_id: 0,
            role_group_id: 0,
            tenant_id: 0,
            rule: {},
            locked_data_mode: 0,
            role_type: 1,
        },
        disabledModuleAccess: {},
        rulePerModule: {},
        levelOption: [],
        tenantOption: [],
        // tenantGroupOption: [],
    }),
    validations() {
        return {
            roleForm: {
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
        };
    },
    computed: {
        isAdd() {
            return this.$route.params.roleId ? false : true;
        },
        pageTitle() {
            return this.Trans.chose(this.AppConfig.packageLocal.moduser.access.children.role.caption);
        },
        title() {
            return this.isAdd ? this.Trans.get("role.roleform.form_add_caption") : "#" + this.roleForm.name;
        },
        // tenantGroup() {
        //     return this.$store.state.tenant.listTenantGroup;
        // },        
        tenantGroup() {
            return this.$store.getters.getTenantGroup;
        },
        //
        isDisabled() {
            return this.roleForm.locked_data_mode == 2;
        },
        canEditRoleType() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_type') || this.UserAuth.isWebdev();
        },
        canEditRoleRule() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_code') || this.UserAuth.isWebdev();
        },
        canEditRoleCode() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_code') || this.UserAuth.isWebdev();
        },
        canEditLevel() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_level') || this.UserAuth.isWebdev();
        },
        canEditLockedDataMode() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_locked_data_mode') || this.UserAuth.isWebdev();
        },
        canEditRoleGroupId() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_group_id') || this.UserAuth.isWebdev();
        }
    },
    methods: {
        // untuk cek banyaknya titik pada rule key, karena parent dan child rule dipisah oleh titik
        calcPadding(key) {
            return (key.split(".").length - 2) * 40;
        },
        /**
         * cek apakah curTenantGroup (tenant_group_id dari menu) menampilkan menu atau tidak
         * param :
         *      curTenantGroup : array berisi list id group tenant menu yg dicek (dari tenant_group_id di item access nya)
         * 
         * return 
         */
        isInGroup(curTenantGroup) {
            // jika multi tenant aktif
            if(this.AppConfig.system.multitenant.active){
                // jika tenant_group_id menu yg dicek berbentuk array, maka detek bandingkan dengan activeGroup nya
                if(curTenantGroup.length && curTenantGroup.length > 0){
                    var arr = this.tenantGroup;// list id tenant group tenant aktif                    
                    //jika tidak ada group berarti sedang di tenant manager
                    if (arr.length == undefined)                        
                        arr = [0];
                    
                    return curTenantGroup.some(r => arr.indexOf(r) >= 0);
                }else{
                    return curTenantGroup == 0 || (curTenantGroup == 1 && !isOnTenantManager) || (curTenantGroup == 2 && isOnTenantManager);
                }          
            }else{
                return curTenantGroup == 0 || curTenantGroup == 1;
            }            
        },
        // cek apakah tenant_group_id terlampir (rule) ditampilkan di role ini
        // isGroupTenantAllowed(tenant_group_id) {
        //     if (this.AppConfig.system.multitenant.active) {
        //         // this.roleForm.tenant_group_id == 0 berarti role nya punya akses ke semua rule
        //         // tenant_group_id == 0 berarti rule nya bisa diakses oleh semua role
        //         if (!(this.roleForm.tenant_group_id == 0 || tenant_group_id == 0 || tenant_group_id.includes(this.roleForm.tenant_group_id))) return false;
        //     }
        //     return true;
        // },
        //kosongkan/uncek semua checklist di roleform
        setEmptyRole() {
            var tmp = {};
            _.forEach(this.AppConfig.acl, (v, k) => {
                tmp[k] = { has_access: 0 };

                this.disabledModuleAccess[k] = true;
                this.rulePerModule[k] = {};

                _.forEach(v.children, (rule, ruleKey) => {
                    tmp[ruleKey] = {
                        has_access: 0,
                        c: 0,
                        r: 0,
                        u: 0,
                        d: 0,
                    };

                    this.rulePerModule[k][ruleKey] = ruleKey;
                });
            });
            this.roleForm.rule = tmp;
        },
        //load data role yang akan diedit
        loadRole() {
            let params = {};
            if(!this.canEditRoleType)
                params.role_type = 1;

            this.$store
                .dispatch("role/getRole", {id: this.$route.params.roleId,params:params})
                .then((res) => {
                    this.checkSystemRole(res);

                    //copy semua data role ke roleform nya kecuali field rule, karena field rule akan di-assign
                    //selanjutnya sesuai format yang digunakan di roleform ini
                    _.forEach(res, (v, k) => {
                        if (k != "rule") this.roleForm[k] = v;
                    });

                    //set rule kosong untuk base role
                    var tmp = JSON.parse(JSON.stringify(this.roleForm.rule));

                    if (res.rule != null) {
                        _.forEach(this.roleForm.rule, (v, k) => {
                            if (res.rule[k] != undefined) {
                                if (res.rule[k]["has_access"] && this.disabledModuleAccess[k] != undefined) this.disabledModuleAccess[k] = false;

                                _.forEach(res.rule[k], (v2, k2) => {
                                    tmp[k][k2] = v2 ? 1 : 0;
                                });
                            }
                        });
                    }

                    this.roleForm.rule = tmp;
                    this.pageLoaded = true;
                })
                .catch((res) => {
                    this.Web.showAlert({ text: "Role not found", style: "warning" });
                    this.$router.push({ name: "role.list" });
                });
        },
        //enable/disable pilihan rule per module
        setModule(moduleKey, val) {
            var tmp = JSON.parse(JSON.stringify(this.disabledModuleAccess));
            tmp[moduleKey] = val ? false : true;
            this.disabledModuleAccess = tmp;
        },
        //ceklis semua rule di module tertentu
        checkModuleAll(moduleKey) {
            var tmp = JSON.parse(JSON.stringify(this.roleForm.rule));
            _.forEach(this.rulePerModule[moduleKey], (v2, k2) => {
                tmp[k2] = {
                    has_access: 1,
                    c: 1,
                    r: 1,
                    u: 1,
                    d: 1,
                };
                _.forEach(this.AppConfig.acl[moduleKey]["children"][k2]["crud"], (v3, k3) => {
                    if (v3 == 0 || v3 == 0) tmp[k2][k3] = 0;
                });
            });
            this.roleForm.rule = tmp;
        },
        //unceklis semua rule di module tertentu
        uncheckModuleAll(moduleKey) {
            var tmp = JSON.parse(JSON.stringify(this.roleForm.rule));
            _.forEach(this.rulePerModule[moduleKey], (v2, k2) => {
                tmp[k2] = {
                    has_access: 0,
                    c: 0,
                    r: 0,
                    u: 0,
                    d: 0,
                };
            });
            this.roleForm.rule = tmp;
        },
        //format nama role agar sesuai aturan penamaan role nya
        formatRoleCodeFromRoleName(v) {
            this.$v.roleForm.name.$touch();
            if (this.roleForm.role_code == "") {
                this.roleForm.role_code = this.roleForm.name.toLowerCase().replace(/[^a-zA-Z0-9_]/g, "_");
            }
        },
        //format penamaan role_code agar sesuai aturan penamaan role_code nya
        formatRoleCode(v) {
            this.$v.roleForm.role_code.$touch();
            this.roleForm.role_code = this.roleForm.role_code.replace(/[^a-zA-Z0-9_]/g, "_");
        },
        //cek apakah salah satu crud atau has_access ada yg tercek
        isAnyCheck(rule) {
            if (rule.has_access != 0 || rule.c != 0 || rule.r != 0 || rule.u != 0 || rule.d != 0) return true;
            return false;
        },
        save() {
            /**
             * memastikan child yg parent Module nya tidak diceklis agar di 0 kan jg semau
             */
            _.forEach(this.disabledModuleAccess, (v, k) => {
                _.forEach(this.rulePerModule[k], (v2, k2) => {
                    //jika module disabled maka uncheck semua hak akses nya
                    if (v) {
                        this.roleForm.rule[k2] = {
                            has_access: 0,
                            c: 0,
                            r: 0,
                            u: 0,
                            d: 0,
                        };
                        //jika module enable pastikan .has_access terceklis saat ada salah satu crud yg terceklis
                    } else {
                        this.roleForm.rule[k2]["has_access"] = 
                            this.roleForm.rule[k2]["has_access"] || 
                            this.roleForm.rule[k2]["c"] || 
                            this.roleForm.rule[k2]["r"] || 
                            this.roleForm.rule[k2]["u"] || 
                            this.roleForm.rule[k2]["d"] ? 1 : 0;
                    }
                });
            });

            /**
             * memastikan child yg parentnya tidak terceklis agar sama2 tidak terceklis
             */
            _.forEach(this.AppConfig.acl, (moduleRule, moduelName) => {
                _.forEach(moduleRule.children, (rule, ruleKey) => {
                    //jika parent nya disable maka child nya juga di uncek
                    if (!this.roleForm.rule[rule.parent].has_access || !this.isInGroup(rule.tenant_group_id) || !this.UserAuth.hasAccess(ruleKey)) {
                        this.roleForm.rule[ruleKey] = {
                            has_access: 0,
                            c: 0,
                            r: 0,
                            u: 0,
                            d: 0,
                        };
                    } else {
                        this.roleForm.rule[ruleKey] = {
                            has_access: !this.UserAuth.hasAccess(ruleKey, "has_access") ? 0 : this.roleForm.rule[ruleKey]["has_access"],
                            c: !this.UserAuth.hasAccess(ruleKey, "c") ? 0 : this.roleForm.rule[ruleKey]["c"],
                            r: !this.UserAuth.hasAccess(ruleKey, "r") ? 0 : this.roleForm.rule[ruleKey]["r"],
                            u: !this.UserAuth.hasAccess(ruleKey, "u") ? 0 : this.roleForm.rule[ruleKey]["u"],
                            d: !this.UserAuth.hasAccess(ruleKey, "d") ? 0 : this.roleForm.rule[ruleKey]["d"],
                        };
                    }
                });
            });

            this.$v.$touch();
            if (this.$v.$invalid) {
                this.Web.showAlert({
                    type: "danger",
                    title: this.Trans.get("alert.form_must_complete_title"),
                    text: this.Trans.get("alert.form_must_complete_text"),
                });
            } else {
                if (this.isAdd) {
                    
                    if (!this.UserAuth.hasAccess(this.accessRuleKey, "c")) {
                        //goto dashboard current tenant
                        this.Web.goToCurrentTenant();
                        this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), style: "warning" });
                        return false;
                    }
                    this.$store
                        .dispatch("role/create", this.roleForm)
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
                        .dispatch("role/update", { data: this.roleForm, id: this.roleForm.id })
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
        loadRoleGroup() {         
            this.Repo('moduserRoleGroup')
                .readList({saveState:false})
                .then((res) => {
                    if (res.count == 0) {
                        this.Web.showAlert({
                            title: this.Trans.get("alert.info_title"),
                            text: this.Trans.get("lang.no_data"),
                            type: "info",
                        });
                    }else{
                        this.selectRoleGroup = [{text: '-- Ungrouped --', value: 0},]
                        res.data.forEach((v,i) => {
                            this.selectRoleGroup.push({
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
                                attribute: this.Trans.get('role.role_group.name'),
                            }) +
                            "<br>\n" +
                            res.message,
                        type: "warning",
                    });
                });
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
        this.initView();
        this.loadRoleGroup();

        this.setEmptyRole();

        if (this.isAdd) {
            this.pageLoaded = true;
        }else{
            this.loadRole();
        }

        var startI = parseInt(this.UserAuth.getUser('level')) + 1;

        var endI = parseInt(this.AppConfig.packageLocal.moduser.user_role.user_level.max) + 1;
        if (startI < parseInt(this.AppConfig.packageLocal.moduser.user_role.user_level.min)) startI = parseInt(this.AppConfig.packageLocal.moduser.user_role.user_level.max);

        //load level
        for (var i = startI; i < endI; i++) {
            this.levelOption[i] = i;
        }

        //load tenant group jika fitur multitenant aktif
        // if (this.AppConfig.system.multitenant.active == 1 && this.Web.getTenant.is_main) {
        //     this.$store.dispatch("listTenantGroup").then((res) => {
        //         var tmp = [{ value: 0, text: this.Trans.get("role.roleform.tenant_group_select_all_group") }];
        //         _.forEach(res, (v, k) => {
        //             tmp.push({
        //                 value: parseInt(v.id),
        //                 text: v.name,
        //             });
        //         });
        //         this.tenantGroupOption = tmp;
        //     });
        //     if (this.AppConfig.packageLocal.moduser.role_hidden_field.includes("tenant_group")) {
        //         this.showTenantGroup = false;
        //     }
        // } else {
        //     this.showTenantGroup = false;
        // }

        this.Web.setBodyWithPadding(false);
    },
    // destroyed() {
    //     this.Web.setBodyWithPadding(true);
    // }
};
</script>
