$('#add-image').click(function() {

    ///On récupérer les numéros de futurs champs que vont être créer

    ///const index = $('#form_images div.form-group').length;

    const index = +$('#widgets-counter').val();

    ///console.log(index);

    // On récupère le prototype des entrées

    const tmpl = $('#form_images').data('prototype').replace(/__name__/g, index);

    ///console.log(tmpl);

    $('#form_images').append(tmpl);

    $('#widgets-counter').val(index + 1);

    ///On gère le button supprimer

    handleDeleteButtons();

    function handleDeleteButtons() {

        $('button[data-action="delete"]').click(function() {

            const target = this.dataset.target;

            ///console.log(target);

            $(target).remove();
        });
    }

    function updateCounter() {

        const count = +$('#form_images div.form-group').length;

        $('#widgets-counter').val(count);
    }
});

// On appelle la fonction handleDeleteButtons lorsque la page recharge, au cas où on voudrait supprimer des photos lors des éditions

updateCounter()

handleDeleteButtons();
