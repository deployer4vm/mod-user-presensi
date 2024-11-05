<template>  
    <div>
        <component v-bind:is="dashboardTemplate" :featureList="featureTemplate" />
    </div>
</template>
<script>
import configDashboard from 'node_modules/../app/MainApp/resources/js/dashboard.js';
export default {
    name: "dashboard-handler",
    data: () => ({

    }),
    computed: {        
        // shared view data
        viewData: {
            get() {
                return this.$store.state.moduserView.dataDashboard;
            },
            set(value) {
                this.$store.commit("moduserView/setDataDashboard", value);
            }
        },
        dashboardTemplate() {            
            return configDashboard.template[this.UserAuth.getAuthRole()['dashboard']['template_code']]
        },
        featureTemplate() {
            var tmpFeature = {};          
            if(configDashboard.feature && this.UserAuth.getAuthRole()['dashboard']['feature'])  
                _.forEach(this.UserAuth.getAuthRole()['dashboard']['feature'],(v,k)=>{
                    if(configDashboard.feature[v])
                        tmpFeature[v] = configDashboard.feature[v];
                });
            return tmpFeature;
        }

    },
    created() {
        // console.log(this.UserAuth.getAuthRole()['dashboard'])
        // console.log(configDashboard,configDashboard.template);
    }

}
</script>