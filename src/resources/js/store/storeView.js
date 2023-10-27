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
    }
};

const getters = {  
    dataNotif(state) {
        return state.dataNotif;
    },
    //--- config
    dataConfigSetting(state) {
        return state.dataConfigSetting;
    },
};

const mutations = {
    setDataNotif(state, value) {
        state.dataNotif = value;
    },
    //--- config
    setDataConfigSetting(state, value) {
        state.dataConfigSetting = value;
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
