import React, { useState, useEffect } from 'react';

import { AutocompleteField } from '@centreon/ui';

import { Listing } from '../models';
import useCancelTokenSource from '../useCancelTokenSource';
import { getData } from '../api';

interface Entity {
  id: number;
  name: string;
}

const ConnectedAutocompleteField = ({
  endpoint,
  searchField,
  ...props
}): JSX.Element => {
  const [options, setOptions] = useState<Array<Entity>>();
  const [open, setOpen] = useState(false);
  // const [autocompleteText, setAutocompleteText] = useState<string>();

  const { token, cancel } = useCancelTokenSource();

  const loadOptions = (searchParams = ''): void => {
    getData<Listing<Entity>>({
      endpoint: `${endpoint}${searchParams}`,
      requestParams: { token },
    })
      .then((retrievedOptions) => {
        console.log(retrievedOptions);
        // setOptions(retrievedOptions.result);
      })
      .catch(() => setOptions([]));
  };

  const changeText = (event): void => {
    const searchParams = `&search={"${searchField}":"$lk":"${event.target.value}}"`;

    loadOptions(searchParams);
  };

  const doOpen = () => {
    setOpen(true);
  };

  const close = () => {
    setOpen(false);
  };

  useEffect(() => {
    if (!open) {
      return;
    }
    loadOptions();
    return (): void => cancel();
  }, [open]);

  return (
    <AutocompleteField
      onOpen={doOpen}
      onClose={close}
      options={options || []}
      onTextChange={changeText}
      {...props}
    />
  );
};

export default ConnectedAutocompleteField;
