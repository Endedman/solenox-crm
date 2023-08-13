/**
 * https://gist.github.com/Minecrell/755e53aced83ab48513f#gistcomment-3494152
 */
(()=>{

document.querySelectorAll( '.mc-k' ).forEach( element => {

    var characters = 'aáäâbcçdéëêfghiïíîjklmnoóöôøpqrsßtuúüûvwxyz1234567890',
        length = element.innerText.length;

    setInterval( () => {

        var newString = '';

        for( var i = 0; i < length; i++ ) {
            var newCharacter = characters[ Math.floor( Math.random() * characters.length ) ];
            if( Math.random() > 0.5 ) newCharacter = newCharacter.toUpperCase();
            newString += newCharacter;
        }

        element.innerText = newString;

    }, 25 )

} )

})()