import globals from "@/globals";

const state = {
    dataNotif: {// general configDb di module ini
        reloadNotif: false,
    },
};

const getters = {  
    dataNotif(state) {
        return state.dataNotif;
    },
};

const mutations = {
    setDataNotif(state, value) {
        state.dataNotif = value;
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
