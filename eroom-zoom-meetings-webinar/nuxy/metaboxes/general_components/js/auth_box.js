Vue.component('wpcfto_auth_box', {
    props: ['fields', 'field_name', 'field_id', 'field_value'],
    data: function () {
        return {
            value: '',
            mount_status: false
        }
    },
    template: `
        <div class="wpcfto_generic_field wpcfto_generic_field__auth_box"
             v-bind:class="[field_name, fields.status ? 'auth-box-' + fields.status : 'auth-box-error']">
            <div class="wpcfto_auth_box__content">
                <div v-if="fields.icon" class="auth_box_icon">
                    <i :class="fields.icon"></i>
                </div>
                <div class="auth_box_text">
                    <div v-if="fields.title" class="auth_box_title" v-html="fields.title"></div>
                    <div v-if="fields.description" class="auth_box_description" v-html="fields.description"></div>
                </div>
            </div>
            <div v-if="fields.buttons" class="wpcfto_auth_box__buttons">
                <a v-for="(button, index) in fields.buttons"
                   :key="index"
                   v-if="button.url || button.text"
                   :href="button.url"
                   class="button"
                   :class="button.class || 'button-secondary'"
                   :target="button.target || '_self'"
                   rel="nofollow">
                    {{ button.text }}
                </a>
            </div>
        </div>
    `
});
