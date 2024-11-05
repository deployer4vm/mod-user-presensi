<template>  
    <div>
        <moduser-welcome />
    </div>
</template>
<script>
import moduserWelcome from './feature/moduser_welcome.vue';
export default {
    name: "moduser-dashboard-default-blank",    
    components: {
        moduserWelcome,
    },
    data: () => ({

    })
}
</script>