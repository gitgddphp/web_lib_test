window.addEvent('domready', function() {

    hljs.initHighlightingOnLoad();

    $$('a').addEvent('click', function() { this.blur() });

    var tab_selectors = $$('.tabs a');
    var tabs = $$('.tab');

    tabs.each(function(tab) {
        tab.set('tween', {duration: 250});
    });

    tab_selectors.each(function(selector, index) {
        selector.addEvent('click', function(e) {
            e.stop();
            tab_selectors.removeClass('selected');
            this.addClass('selected');
            tabs.setStyle('display', 'none');
            tabs[index].setStyles({
                'opacity':  0,
                'display': 'block'
            });
            tabs[index].tween('opacity', 1);
        });
    });

});