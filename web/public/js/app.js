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
		amount: amount,
		money: money,
		likes: 0,
		commentText: '',
		boosterpacks: [],
		boosterpack_one: null,
		boosterpack_items: [],

		buy_boosterpack: null,
		buy_boosterpack_item: null,
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
			.get('/post/get_all_posts')
			.then(function (response) {
				self.posts = response.data.posts;
			})

		axios
			.get('/boosterpack/get_boosterpacks')
			.then(function (response) {
				self.boosterpacks = response.data.boosterpacks;
			})
	},
	methods: {
		logout: function () {
				axios.post('/auth/logout')
					.then(function (response) {
						if (response.data.status === STATUS_SUCCESS) {
							location.reload();
						} else {
							alert(response.data.error_message);
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

				axios.post('/auth/login', form)
					.then(function (response) {
						if (response.data.status === STATUS_SUCCESS) {
							location.reload();
						} else {
							setTimeout(function () {
								$('#loginModal').modal('hide');
							}, 500);
							alert(response.data.error_message);
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
					'/comment/add',
					comment
				).then(function (response) {
					if (response.data.status !== STATUS_SUCCESS) {
						alert(response.data.error_message);
						return;
					}

					//Reload post for new comment
					self.openPost(self.post.id);
				});
			}

		},
		refill: function () {
			var self = this;
			if (self.addSum === 0) {
				self.invalidSum = true
			} else {
				self.invalidSum = false
				sum = new FormData();
				sum.append('sum', self.addSum);
				axios.post('/money/add', sum)
					.then(function (response) {
						if (response.data.status !== STATUS_SUCCESS) {
							if (response.data.error_message) {
								alert(response.data.error_message);
							}
							return;
						}

						self.money = response.data.wallet_balance;

						setTimeout(function () {
							$('#addModal').modal('hide');
						}, 500);
					})
			}
		},
		openPost: function (id) {
			var self= this;
			axios
				.get('/post/get_post/' + id)
				.then(function (response) {
					if (response.data.status !== STATUS_SUCCESS) {
						if (response.data.error_message) {
							alert(response.data.error_message);
						}
						return;
					}

					self.post = response.data.post;
					if (self.post) {
						setTimeout(function () {
							$('#postModal').modal('show');
						}, 500);
					}
				})
		},
		addLike: function (type, id) {
			var self = this;
			const url = '/like/like_' + type + '/' + id;

			if (self.amount <= 0) {
				return;
			}

			axios
				.get(url)
				.then(function (response) {
					if (response.data.status !== STATUS_SUCCESS) {
						alert(response.data.error_message);
						return;
					}

					self.amount = response.data.likes_balance;
					self.openPost(self.post.id);
				})

		},
		boosterpackInfo: function (id) {
			var self= this;
			axios.get('/boosterpack/get_boosterpack_info/' + id)
				.then(function (response) {
					if (response.data.status !== STATUS_SUCCESS) {
						if (response.data.error_message) {
							alert(response.data.error_message);
						}
						return;
					}

					self.boosterpack_one = response.data.boosterpack;
					self.boosterpack_items = response.data.boosterpack_items
					if (self.boosterpack_one) {
						setTimeout(function () {
							$('#boosterpackModal').modal('show');
						}, 200);
					}
				})
		},
		buyPack: function (id) {
			var self= this;
			var pack = new FormData();
			pack.append('id', id);
			axios.post('/boosterpack/buy_boosterpack', pack)
				.then(function (response) {
					if (response.data.status !== STATUS_SUCCESS) {
						if (response.data.error_message) {
							alert(response.data.error_message);
						}
						return;
					}

					$('#boosterpackModal').modal('hide');

					self.amount = response.data.user.likes_balance
					self.money = response.data.user.wallet_balance;

					self.buy_boosterpack = response.data.boosterpack;
					self.buy_boosterpack_item = response.data.boosterpack_item;

					setTimeout(function () {
						$('#amountModal').modal('show');
					}, 500);
				})
		}
	}
});

