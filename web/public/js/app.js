const STATUS_SUCCESS = 'success';
const STATUS_ERROR = 'error';
var app = new Vue({
	el: '#app',
	data: {
		login: '',
		pass: '',
		post: false,
		invalidLogin: false,
		invalidPass: false,
		invalidSum: false,
		posts: [],
		addSum: 0,
		amount: 0,
		likes: 0,
		commentText: '',
		boosterpacks: [],
	},
	computed: {
		test: function () {
			var data = [];
			return data;
		}
	},
	created(){
		var self = this
		axios
			.get('/main_page/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})

		axios
			.get('/main_page/get_boosterpacks')
			.then(function (response) {
				self.boosterpacks = response.data.boosterpacks;
			})
        axios
            .get('/main_page/get_likes_balance')
            .then(function (response) {
                self.likes = response.data.likes;
            })
	},
	methods: {
		logout: function () {
            axios.post('/main_page/logout')
                .then(function (response) {
                    if (response.data.status === 'success') {
                        location.reload();
                    }
                })
		},
		logIn: function () {
			var self= this;
			if(self.login === ''){
				self.invalidLogin = true
			}
			else if(self.pass === ''){
				self.invalidLogin = false
				self.invalidPass = true
			}
			else{
				self.invalidLogin = false
				self.invalidPass = false

				form = new FormData();
				form.append("login", self.login);
				form.append("password", self.pass);

				axios.post('/main_page/login', form)
					.then(function (response) {
						if (response.data.status === 'success') {
							location.reload();

                            setTimeout(function () {
                                $('#loginModal').modal('hide');
                            }, 500);
						} else if (response.data.status === 'info') {
                            if (response.data.error === 'invalidLogin') {
                                self.invalidLogin = true;
                            }

                            if (response.data.error === 'invalidPass') {
                                self.invalidPass = true;
                            }
                        }
					})
			}
		},
		addComment: function(id) {
			var self = this;
			if(self.commentText) {

				var comment = new FormData();
				comment.append('postId', id);
				comment.append('commentText', self.commentText);

				axios.post(
					'/main_page/comment',
					comment
				).then(function (response) {
                    if (response.data.status === STATUS_SUCCESS) {
                        self.post.coments.push(response.data.comment);
                        self.commentText = '';
                    }
				});
			}

		},
		refill: function () {
			var self = this;
            if (self.addSum === 0 || isNaN(+self.addSum)) {
                self.invalidSum = true;
            } else {
                self.invalidSum = false;

                sum = new FormData();
                sum.append('sum', self.addSum);

                axios.post('/main_page/add_money', sum)
                    .then(function (response) {
                        console.log(response.data)
                        setTimeout(function () {
                        	$('#addModal').modal('hide');
                        }, 500);
                    });
            }
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/main_page/get_post/' + id)
				.then(function (response) {
					self.post = response.data.post;
					if(self.post){
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		addLike: function (type, entity) {
            let self = this;
			const url = '/main_page/like_' + type + '/' + entity.id;
			axios
				.get(url)
				.then(function (response) {
                    if (response.data.status === STATUS_SUCCESS) {
                        entity.likes = +response.data.likes;
                        self.likes--;
                    }
				})
		},
		buyPack: function (id) {
			var self= this;
			var pack = new FormData();
			pack.append('id', id);
			axios.post('/main_page/buy_boosterpack', pack)
				.then(function (response) {
					self.amount = response.data.amount
					if(self.amount !== 0){
                        self.likes += response.data.amount;
						setTimeout(function () {
							$('#amountModal').modal('show');
						}, 500);
					}
				})
		}
	}
});

