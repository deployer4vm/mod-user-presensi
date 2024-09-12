import globals from "@/globals";

const state = {
    dataNotif: {// general configDb di module ini
        reloadNotif: false,
    },

    /**
     * CONFIG
     * -------------------------------------------------------------------------
     */
    dataConfigSetting: {
        //--- auth
        auth: {
            curTab: "password",
            accessRuleKey:'moduser.config.auth',

            // password
            password: {
                accessRuleKey:'moduser.config.auth.password',
                loaded: false,

                form:{
                    password_minlength: 8,
                },
            },

            // otp
            otp: {
                accessRuleKey:'moduser.config.auth.otp',
                loaded: false,

                form:{
                    otp_enabled: 1,
                    otp_digit: 8,
                    otp_timeout: 5,
                    otp_channel_email: 1,
                    otp_channel_sms: 0,
                    otp_channel_wa: 0,
                },
            },

            // pin
            pin: {
                accessRuleKey:'moduser.config.auth.pin',
                loaded: false,

                form:{
                    pin: 1,
                    pin_digit: 8,
                },
            },

            // login
            login: {
                accessRuleKey:'moduser.config.auth.login',
                loaded: false,

                form:{
                    login_rememberme: 0,
                    login_forgotpassword: 1
                },
            },
        },
        //--- registration
        registration: {
            curTab: "general",
            accessRuleKey:'moduser.config.registration',

            // general
            general: {
                loaded: false,

                form:{
                    enable: 0,
                    auto_active: 1,
                    admin_send_activation_email: 1,
                    tos_confirm: 0,
                    default_role_code: 'admin'
                },
            },

            // tos
            tos: {
                loaded: false,

                form:{
                    tos_content: ''
                },
            },
        }
    },

    /**
     * USER
     * -------------------------------------------------------------------------
     */
    // user -> group
    dataUserGroup: {
        defaultModalSize: "md",
        accessRuleKey: "moduser.user.group",

        listData: {
            count:0,
            searchString: "",
            sortBy: "id",
            sortDesc: false,
            perPage: 10,
            curPage: 1,
            perPageOption: [10, 20, 50, 100],
            noResultsText: "Loading...",
            loadParams: {},
        },

        formData: {
            isAdd:false,
            form: {
                id: 0,
                code: '',
                name: '',
                description: '',
                locked_data_mode: 0
            },
            formEmpty: {
                id: 0,
                code: '',
                name: '',
                description: '',
                locked_data_mode: 0
            },
        }
    },
    /**
     * ROLE
     * -------------------------------------------------------------------------
     */
    // role
    dataRole:{
        formData: {
            isAdd:false,
            form: {
                id: 0,
                // tenant_group_id: 0,
                tenant_id: 0,
                //
                role_group_id: 0,
                role_group_code: '',
                datarule_id: 0,
                datarule_code: '',
                dashboard_type:0,
                //
                role_code: "",
                level: 2,
                role_type: 1,
                system_user: 0,
                name: "",
                rule: {},
                locked_data_mode: 0,
                //
                bypass_rule:0,
                bypass_datarule:0,
                bypass_dashboard:0,
                bypass_notification:0,
                //
                is_global:0,
                global_bypass_rule:0,
                global_bypass_datarule:0,
                global_bypass_dashboard:0,
                global_bypass_notification:0,
                //
                // global:{},// format
                global:[
                    {// index 0 untuk general
                        tenant_id: 0,
                        global_bypass_rule: 0,
                        global_bypass_datarule: 0,
                        global_bypass_dashboard: 0,
                        global_bypass_notification: 0,
                    }
                ],
            },
            formEmpty: {
                id: 0,
                // tenant_group_id: 0,
                tenant_id: 0,
                //
                role_group_id: 0,
                role_group_code: '',
                datarule_id: 0,
                datarule_code: '',
                dashboard_type:0,
                //
                role_code: "",
                level: 2,
                role_type: 1,
                system_user: 0,
                name: "",
                rule: {},
                locked_data_mode: 0,
                //
                bypass_rule:0,
                bypass_datarule:0,
                bypass_dashboard:0,
                bypass_notification:0,
                //
                is_global:0,
                global_bypass_rule:0,
                global_bypass_datarule:0,
                global_bypass_dashboard:0,
                global_bypass_notification:0,
                //
                // global:{},// format
                global:[
                    {// index 0 untuk general
                        tenant_id: 0,
                        global_bypass_rule: 0,
                        global_bypass_datarule: 0,
                        global_bypass_dashboard: 0,
                        global_bypass_notification: 0,
                    }
                ],

            },            
            formGlobalEmpty:{// tenant id
                tenant_id: 0,
                global_bypass_rule:0,
                global_bypass_datarule:0,
                global_bypass_dashboard:0,
                global_bypass_notification:0,
            },
        }
    },
    // role -> group
    dataRoleGroup: {
        defaultModalSize: "md",
        accessRuleKey: "moduser.role.group",

        listData: {
            count:0,
            searchString: "",
            sortBy: "id",
            sortDesc: false,
            perPage: 10,
            curPage: 1,
            perPageOption: [10, 20, 50, 100],
            noResultsText: "Loading...",
            loadParams: {},
        },

        formData: {
            isAdd:false,
            form: {
                id: 0,
                code: '',
                has_model: 0,
                model: '',
                dashboard_type: 0,
                can_selected_on_create: 1,
                name: '',
                description: '',
                locked_data_mode: 0

            },
            formEmpty: {
                id: 0,
                code: '',
                has_model: 0,
                model: '',
                dashboard_type: 0,
                can_selected_on_create: 1,
                name: '',
                description: '',
                locked_data_mode: 0
            },
        }
    },
    // role -> level group
    dataRoleLevelGroup: {
        defaultModalSize: "md",
        accessRuleKey: "moduser.role.levelGroup",

        listData: {
            count:0,
            searchString: "",
            sortBy: "id",
            sortDesc: false,
            perPage: 10,
            curPage: 1,
            perPageOption: [10, 20, 50, 100],
            noResultsText: "Loading...",
            loadParams: {},
        },

        formData: {
            isAdd:false,
            form: {
                id: 0,
                code: '',
                level_start: 1,
                level_end: 99,
                name: '',
                description: '',
                locked_data_mode: 0

            },
            formEmpty: {
                id: 0,
                code: '',
                level_start: 1,
                level_end: 99,
                name: '',
                description: '',
                locked_data_mode: 0
            },
        }
    }
};

const getters = {  
    dataNotif(state) {
        return state.dataNotif;
    },
    /**
     * CONFIG
     * -------------------------------------------------------------------------
     */
    dataConfigSetting(state) {
        return state.dataConfigSetting;
    },
    /**
     * USER
     * -------------------------------------------------------------------------
     */
    // user -> group
    dataUserGroup(state) {
        return state.dataUserGroup;
    },
    /**
     * ROLE
     * -------------------------------------------------------------------------
     */
    // role
    dataRole(state) {
        return state.dataRole;
    },
    // role -> group
    dataRoleGroup(state) {
        return state.dataRoleGroup;
    },
    // role -> level group
    dataRoleLevelGroup(state) {
        return state.dataRoleLevelGroup;
    },
};

const mutations = {
    setDataNotif(state, value) {
        state.dataNotif = value;
    },
    /**
     * CONFIG
     * -------------------------------------------------------------------------
     */
    setDataConfigSetting(state, value) {
        state.dataConfigSetting = value;
    },
    /**
     * USER
     * -------------------------------------------------------------------------
     */
    // user -> group
    setDataUserGroup(state, value) {
        state.dataUserGroup = value;
    },
    /**
     * ROLE
     * -------------------------------------------------------------------------
     */
    // role
    setDataRole(state, value) {
        state.dataRole = value;
    },
    // role -> group
    setDataRoleGroup(state, value) {
        state.dataRoleGroup = value;
    },
    // role -> level group
    setDataRoleLevelGroup(state, value) {
        state.dataRoleLevelGroup = value;
    },
};

const actions = {};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters,
};
