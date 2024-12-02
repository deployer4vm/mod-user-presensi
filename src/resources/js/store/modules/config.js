import globals from "@/globals";
var userApi = globals().AppConfig.endpoint.api.moduser + '/config/';

const state = {
    dashboardList: {
        data: [

        ],
        count: 0,
        limit: 10,
        offset: 0,
        currentPage: 1,
        pageCount: 1
    },
    dashboardForm: {},
    //
    config: {
        tenantList: []
    },
    //
    templateList: [],
};

const getters = {
    getTenantList(state) {
        return state.config.tenantList;
    },
    //
    getDashboardList(state) {
        return state.dashboardList;
    },
    getDashboard(state) {
        return state.dashboardForm;
    },
    //
    getTemplateList(state) {
        return state.templateList;
    }
};

const mutations = {
    setTenantList(state, data) {
        state.config.tenantList = data;
    },
    //
    setDashboardList(state, data) {
        state.dashboardList = data;
    },
    setDashboard(state, data) {
        state.dashboardForm = data;
    },
    //
    setTemplateList(state, data) {
        state.templateList = data;
    }
};

const actions = {    
    listTenant({ commit, dispatch, state }, params) {
        return globals()
            .LocalApi.get(userApi + "tenant")
            .then(res => {
                if (params.saveState == undefined || params.saveState)
                    commit("setTenantList", res.data.data);
                return res.data.data;
            });
    },
    //
    listDashboard({ commit, dispatch, state }, params = {}) {
        return globals()
            .LocalApi.get(userApi + "dashboard")
            .then(res => {
                const saveState = params.saveState !== undefined ? params.saveState : true;
                if (saveState)
                    commit("setDashboardList", res.data.data);
                return res.data.data;
            });
    },
    getDashboard({ commit }, params) {
        return globals()
            .LocalApi.get(userApi + "dashboard/" + params.id,{
                params: params.params
            })
            .then(res => {
                if (params.saveState == undefined || params.saveState)
                    commit("setDashboard", res.data.data);
                return res.data.data;
            });
    },
    createDashboard({commit, dispatch},params) {
        return globals()
            .LocalApi.post(userApi + "dashboard", params.data)
            .then(res => {
                return params.reload==undefined||params.reload?dispatch("listDashboard"):res.data.data;
            });
    },
    updateDashboard({commit, dispatch},params){
        return globals()
            .LocalApi.put(userApi + "dashboard/" + params.id, params.data)
            .then(res => {
                return params.reload==undefined||params.reload?dispatch("listDashboard"):res.data.data;
            });
    },
    deleteDashboard({commit, dispatch},params){
        console.log(params)
        return globals()
            .LocalApi.delete(userApi + "dashboard/" + params.id)
            .then(res => {
                return params.reload==undefined||params.reload?dispatch("listDashboard"):res.data.data;
            });
    },
    //----
    // Config Dashboard
    updateConfigDashboard({ commit, dispatch }, params) {
        return globals()
            .LocalApi.put(userApi + 'dashboard', params.data)
            .then(res => {
                return res.data.data;
            });
    },
    //--- Template
    listTemplate({ commit, dispatch, state }, params = {}) {
        return globals()
            .LocalApi.get(userApi + 'dashboard/template')
            .then(res => {
                const saveState = params.saveState !== undefined ? params.saveState : true;
                if (saveState)
                    commit("setTemplateList", res.data.data);
                return res.data.data;
            });
    },
};

const userConfig = {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};

export default userConfig;
