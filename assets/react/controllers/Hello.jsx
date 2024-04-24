import React from 'react';

export default function (props) {

    const {fullName, lastName, number} = props;

    return   <>
                <div>Hello {fullName}</div>
                <div>NOM {lastName}</div>
                <div>NUMERO {number}</div>
            </>
}
