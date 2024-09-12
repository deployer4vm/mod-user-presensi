<template>
    <div>
        <!-- <header-breadcrumb :pageTitle="pageTitle" :backPath="{ name: 'role.list' }" />
        <b-container>

            <div class="my-3">
                <h4>
                    Edit Rule <b>{{data.formData.form.name}} [{{data.formData.form.role_code}}]</b>
                </h4>
            </div> -->
    
            <!-- input Rule -->
            <div class="my-3" v-if="data.formData.form.role_type==1 && canEditRoleRule">
                
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
                                    <span v-if="data.formData.form.rule[key].has_access" class="d-flex align-items-center justify-content-center rounded-circle overflow-hiddens p-1 bg-success" style="width: 20px; height: 20px;">
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
                                                <b-check class="my-1" :disabled="isDisabled" :value="1" :unchecked-value="0" v-model="data.formData.form.rule[key].has_access" @change="setModule(key, $event)">
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
                                                                    <b-check :value="1" :unchecked-value="0" v-model="data.formData.form.rule[ruleKey].has_access" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(data.formData.form.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'has_access')">
                                                                        {{ Trans.get("role.roleform.has_access") }}
                                                                    </b-check>
                                                                </td>
                                                            </template>
                                                            <template v-else
                                                                ><!-- jika menampilkan check CRUD -->
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="data.formData.form.rule[ruleKey].c" v-if="rule.crud.c == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(data.formData.form.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'c')" class="px-2 m-0" />
                                                                </td>
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="data.formData.form.rule[ruleKey].r" v-if="rule.crud.r == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(data.formData.form.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'r')" class="px-2 m-0" />
                                                                </td>
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="data.formData.form.rule[ruleKey].u" v-if="rule.crud.u == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(data.formData.form.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'u')" class="px-2 m-0" />
                                                                </td>
                                                                <td>
                                                                    <b-check :value="1" :unchecked-value="0" v-model="data.formData.form.rule[ruleKey].d" v-if="rule.crud.d == 1" :disabled="isDisabled || disabledModuleAccess[key] || !isAnyCheck(data.formData.form.rule[rule.parent]) || !UserAuth.hasAccess(ruleKey, 'd')" class="px-2 m-0" />
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
    
            <!-- <div class="text-right mt-3">
                <b-btn variant="primary w-icon" @click="save">
                    <i class="fi fi-rs-disk"></i>
                    <span>{{ Trans.get("lang.save_change") }}</span>
                </b-btn>
            </div>
        </b-container> -->
    </div>
</template>
<!-- Page -->
<style src="@/vendor/styles/pages/users.scss" lang="scss"></style>

<script>
export default {
    name: "pages-role-form-rule",
    props: [
        'roleLoaded',
        'roleSaved'
        // 'placeholder',
        // 'noOptions',
        // 'disabled',
        // 'params'
    ], 
    metaInfo() {
        return { title: this.pageTitle };
    },
    data: () => ({
        curTab:'',
        accessRuleKey: "moduser.role",
        permissions: [], //data acl
        
        disabledModuleAccess: {},
        rulePerModule: {},
        tenantOption: [],
        // tenantGroupOption: [],
    }),  
    watch: {
        'roleLoaded': function(v) { 
            if(v)
                this.initLoadRole(v);
        },
        'roleSaved': function(v) {   
            if(v)
                this.initSave();
        },
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
            return "#" + this.data.formData.form.name;
        },
        // tenantGroup() {
        //     return this.$store.state.tenant.listTenantGroup;
        // },        
        tenantGroup() {
            return this.$store.getters.getTenantGroup;
        },
        //
        isDisabled() {
            return this.data.formData.form.locked_data_mode == 2 || !this.canEditRoleRule;
        },
        needBypassOnly() {
            return this.data.formData.form.is_global == 1 && !isOnTenantManager;
        },
        //digunakan untuk cek hak akses edit role_type, jika tidak ada akses
        //maka hanya bisa edit role dengan role_type = 1 (role biasa)
        canEditRoleType() {
            return this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_role_type') || this.UserAuth.isWebdev();
        },
        canEditRoleRule() {
            return (this.UserAuth.hasAccess(this.accessRuleKey + '.can_edit_rule') || this.UserAuth.isWebdev()) 
                && this.canEditRoleRuleBypass;
        },      
        canEditRoleRuleBypass() {
            return !this.needBypassOnly || (this.data.formData.form.global_bypass_rule==1 || (this.data.formData.form.global_bypass_rule==2 && this.canBypassGlobal))
        }, 
    },
    methods: {
        // cek apakah tenant_group_id terlampir (rule) ditampilkan di role ini
        // isGroupTenantAllowed(tenant_group_id) {
        //     if (this.AppConfig.system.multitenant.active) {
        //         // this.data.formData.form.tenant_group_id == 0 berarti role nya punya akses ke semua rule
        //         // tenant_group_id == 0 berarti rule nya bisa diakses oleh semua role
        //         if (!(this.data.formData.form.tenant_group_id == 0 || tenant_group_id == 0 || tenant_group_id.includes(this.data.formData.form.tenant_group_id))) return false;
        //     }
        //     return true;
        // },
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
                // jika role global maka tampilkan semua
                if(isOnTenantManager && this.data.formData.form.is_global == 1)
                    return true;
                // jika tenant_group_id menu yg dicek berbentuk array, maka detek bandingkan dengan activeGroup nya
                if(curTenantGroup.length && curTenantGroup.length > 0){
                    var arr = this.tenantGroup;// list id tenant group tenant aktif                    
                    //jika tidak ada group berarti sedang di tenant manager
                    if (arr.length == undefined)                        
                        arr = [0];
                    
                    return curTenantGroup.some(r => arr.indexOf(r) >= 0);
                }else{
                    return curTenantGroup == 0 || (curTenantGroup == 1 && !isOnTenantManager) || ((curTenantGroup == 2 || curTenantGroup == 3)  && isOnTenantManager);
                }          
            }else{
                return curTenantGroup == 0 || curTenantGroup == 1;
            }            
        },
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
            this.data.formData.form.rule = tmp;
            this.data.formData.form.formEmpty = JSON.parse(JSON.stringify(tmp));
        },
        initLoadRole(rule){

            if (rule != null) {
                var tmp = JSON.parse(JSON.stringify(this.data.formData.formEmpty.rule));
                _.forEach(this.data.formData.form.rule, (v, k) => {
                    if (rule[k] != undefined) {
                        if (rule[k]["has_access"] && this.disabledModuleAccess[k] != undefined) this.disabledModuleAccess[k] = false;

                        _.forEach(rule[k], (v2, k2) => {
                            tmp[k][k2] = v2 ? 1 : 0;
                        });
                    }
                });
                this.data.formData.form.rule = tmp;
            }

        },
        //enable/disable pilihan rule per module
        setModule(moduleKey, val) {
            var tmp = JSON.parse(JSON.stringify(this.disabledModuleAccess));
            tmp[moduleKey] = val ? false : true;
            this.disabledModuleAccess = tmp;
        },
        //ceklis semua rule di module tertentu
        checkModuleAll(moduleKey) {
            var tmp = JSON.parse(JSON.stringify(this.data.formData.form.rule));
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
            this.data.formData.form.rule = tmp;
        },
        //unceklis semua rule di module tertentu
        uncheckModuleAll(moduleKey) {
            var tmp = JSON.parse(JSON.stringify(this.data.formData.form.rule));
            _.forEach(this.rulePerModule[moduleKey], (v2, k2) => {
                tmp[k2] = {
                    has_access: 0,
                    c: 0,
                    r: 0,
                    u: 0,
                    d: 0,
                };
            });
            this.data.formData.form.rule = tmp;
        },
        //cek apakah salah satu crud atau has_access ada yg tercek
        isAnyCheck(rule) {
            if (rule.has_access != 0 || rule.c != 0 || rule.r != 0 || rule.u != 0 || rule.d != 0) return true;
            return false;
        },
        initSave() {
            /**
             * memastikan child yg parent Module nya tidak diceklis agar di 0 kan jg semau
             */
            _.forEach(this.disabledModuleAccess, (v, k) => {
                _.forEach(this.rulePerModule[k], (v2, k2) => {
                    //jika module disabled maka uncheck semua hak akses nya
                    if (v) {
                        this.data.formData.form.rule[k2] = {
                            has_access: 0,
                            c: 0,
                            r: 0,
                            u: 0,
                            d: 0,
                        };
                        //jika module enable pastikan .has_access terceklis saat ada salah satu crud yg terceklis
                    } else {
                        this.data.formData.form.rule[k2]["has_access"] = 
                            this.data.formData.form.rule[k2]["has_access"] || 
                            this.data.formData.form.rule[k2]["c"] || 
                            this.data.formData.form.rule[k2]["r"] || 
                            this.data.formData.form.rule[k2]["u"] || 
                            this.data.formData.form.rule[k2]["d"] ? 1 : 0;
                    }
                });
            });

            /**
             * memastikan child yg parentnya tidak terceklis agar sama2 tidak terceklis
             */
            _.forEach(this.AppConfig.acl, (moduleRule, moduelName) => {
                _.forEach(moduleRule.children, (rule, ruleKey) => {
                    //jika parent nya disable maka child nya juga di uncek
                    if (!this.data.formData.form.rule[rule.parent].has_access || !this.isInGroup(rule.tenant_group_id) || !this.UserAuth.hasAccess(ruleKey)) {
                        this.data.formData.form.rule[ruleKey] = {
                            has_access: 0,
                            c: 0,
                            r: 0,
                            u: 0,
                            d: 0,
                        };
                    } else {
                        this.data.formData.form.rule[ruleKey] = {
                            has_access: !this.UserAuth.hasAccess(ruleKey, "has_access") ? 0 : this.data.formData.form.rule[ruleKey]["has_access"],
                            c: !this.UserAuth.hasAccess(ruleKey, "c") ? 0 : this.data.formData.form.rule[ruleKey]["c"],
                            r: !this.UserAuth.hasAccess(ruleKey, "r") ? 0 : this.data.formData.form.rule[ruleKey]["r"],
                            u: !this.UserAuth.hasAccess(ruleKey, "u") ? 0 : this.data.formData.form.rule[ruleKey]["u"],
                            d: !this.UserAuth.hasAccess(ruleKey, "d") ? 0 : this.data.formData.form.rule[ruleKey]["d"],
                        };
                    }
                });
            });            
            
            this.$emit('onRuleReady', true);
        },
        checkSystemRole(data) {
            if (data.system_role !== 0) {
                this.$router.push({ name: "role.list" });
                this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), type: "warning" });
            }
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
        // if (!this.UserAuth.hasAccess(this.accessRuleKey)) {
        //     //goto dashboard current tenant
        //     // this.Web.goToCurrentTenant();
        //     this.$router.push({ name: "role.list" });
        //     this.Web.showAlert({ text: this.Trans.get("alert.access_denied"), style: "warning" });
        //     return false;
        // }
        
        this.setEmptyRole();

    },
    // destroyed() {
    //     this.Web.setBodyWithPadding(true);
    // }
};
</script>
