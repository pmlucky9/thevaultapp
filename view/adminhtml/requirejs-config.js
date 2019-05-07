

var config = {
    map: {
        '*': {
            //hideseeksearch: 'TheVaultApp_Magento2/js/hideseek/jquery.hideseek.min',
            framesjs: 'https://cdn.checkout.com/js/frames.js'
        }
    },
    paths: {
	    imgselect: 'TheVaultApp_Magento2/js/msdropdown/jquery.dd.min',
  	},
    shim: {
        imgselect: {
            deps: ['jquery']
        }
    }
};