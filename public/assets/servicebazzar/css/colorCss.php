<?php
	header("Content-type: text/css");

	$color = '#'.$_GET['color'];
?>


.text__base {
    color: <?php echo $color; ?> !important;
}
.bg__base, .pagination .page-item.active .page-link {
    background-color: <?php echo $color; ?> !important;
}
.pagination .page-item .page-link:hover {
    background-color: <?php echo $color; ?>;
    border-color: <?php echo $color; ?>;
}
.btn__base {
    background-color: <?php echo $color; ?>;
    border: 1px solid <?php echo $color; ?>;
}
.btn__base:hover {background: #171616;}
.text__btn {
    color: <?php echo $color; ?>;
}
.btn-outline__base {
    color: <?php echo $color; ?>;
    border: 1px solid <?php echo $color; ?>;
}
.btn-outline__base:hover {
    background-color: <?php echo $color; ?>;
}

.badge__base {
    border: 1px solid <?php echo $color; ?>;
    color: <?php echo $color; ?>;
}
.table thead th{
    background: <?php echo $color; ?>;
}
.image-circle-preview img{
    border: 2px solid <?php echo $color; ?>;
}
.dropdown-menu {
    border-top: 2px solid <?php echo $color; ?>;
}

.header-area .bottom-header ,
.header-area .bottom-header .navbar ul li a:focus,
.header-area .bottom-header .navbar ul li a:active{
    color: <?php echo $color; ?>;
}


.latest-listings .nav-tabs .nav-item .nav-link{
    border: 1px solid <?php echo $color; ?>;
    color: <?php echo $color; ?>;
}
.latest-listings .nav-tabs .nav-link.active,
.latest-listings .nav-tabs .nav-link:focus,
.nav-tabs .nav-link:hover{
    border: 1px solid <?php echo $color; ?>;
    background: <?php echo $color; ?>;
}
.listing-area .listing-category a{
    background: <?php echo $color; ?>;
}
.listing-area .listing-featured{
    background: <?php echo $color; ?>;
}
.listing-area .listing-content h4 a:hover{
    color: <?php echo $color; ?>;
}
.listing-area .listing-content .listing-location i{
    color: <?php echo $color; ?>;
}
.featured .featured-listing-slider .prev{
    background: <?php echo $color; ?>;
}
.featured .featured-listing-slider .next{
    background: <?php echo $color; ?>;
}
.how-work .hw-area::after {
    background-color: <?php echo $color; ?>;
}
.how-work .hw-area::before {
    color: <?php echo $color; ?>;
}
.how-work .hw-area .icon-area{
    background: <?php echo $color; ?>;
}
.location .location-area .listing-count{
    background: <?php echo $color; ?>73;
    border: 1px solid <?php echo $color; ?>;
}
.pricing-area .pricing-card .pricing-head{
    background: <?php echo $color; ?>;
}
.listing-details .listing-details-area .listing-widget .widget-body .widget-feature ul li i{
    color: <?php echo $color; ?>;
}
.author-area .author-contact ul li i{
    color: <?php echo $color; ?>;
}
.author-area .author-social li a{
    background: <?php echo $color; ?>;
}
.verification i{
    color: <?php echo $color; ?>;
}
.contact-element .icon-area{
    background: <?php echo $color; ?>;
}
.footer-area .footer-widget .widget-title:before {
    background: <?php echo $color; ?>4a;
}
.footer-area .footer-widget .widget-title:after {
    background: <?php echo $color; ?>;
}
.footer-area .footer-widget .widget-body ul li a:hover{
    color: <?php echo $color; ?>;
}
.footer-area .footer-widget .widget-body .subscribe-form button{
    background: <?php echo $color; ?>;
    border: 1px solid <?php echo $color; ?>;
}
.pagination .page-item .page-link {
    border: 1px solid <?php echo $color; ?>33;
}
.latest-listings .nav-tabs .nav-item .nav-link{
    border: 1px solid <?php echo $color; ?>;
    background: <?php echo $color; ?>12;
}

.location .location-area .listing-count{
    background: <?php echo $color; ?>73;
}

.form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: <?php echo $color; ?>3b;
}

.latest-listings .nav-tabs .nav-link.active, .latest-listings .nav-tabs .nav-link:focus, .nav-tabs .nav-link:hover{
	border: 1px solid <?php echo $color; ?>;
    background: <?php echo $color; ?>;
}

.latest-listings .nav-tabs .nav-link.active{
    color: #fff !important;
}

.preloader svg path {
  stroke: <?php echo $color; ?>;
}