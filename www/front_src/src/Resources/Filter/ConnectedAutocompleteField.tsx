import React, { useState, useEffect } from 'react';

import { AutocompleteField, AutocompleteFieldProps } from '@centreon/ui';

import { Listing } from '../models';
import useCancelTokenSource from '../useCancelTokenSource';
import { getData } from '../api';

interface Entity {
  id: number;
  name: string;
}

interface Props {
  baseEndpoint: string;
  getSearchEndpoint: (searchField: string) => string;
}

const ConnectedAutocompleteField = ({
  baseEndpoint,
  getSearchEndpoint,
  ...props
}: Props & AutocompleteFieldProps): JSX.Element => {
  const [options, setOptions] = useState<Array<Entity>>();
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(true);

  const { token, cancel } = useCancelTokenSource();

  const loadOptions = (endpoint): void => {
    setLoading(true);
    getData<Listing<Entity>>({
      endpoint,
      requestParams: { token },
    })
      .then((retrievedOptions) => {
        setOptions(retrievedOptions.result);
      })
      .catch(() => setOptions([]))
      .finally(() => setLoading(false));
  };

  const changeText = (event): void => {
    loadOptions(getSearchEndpoint(event.target.value));
  };

  const doOpen = (): void => {
    setOpen(true);
  };

  const close = (): void => {
    setOpen(false);
  };

  useEffect(() => {
    return (): void => cancel();
  }, []);

  useEffect(() => {
    if (!open) {
      return;
    }

    loadOptions(baseEndpoint);
  }, [open]);

  return (
    <AutocompleteField
      onOpen={doOpen}
      onClose={close}
      options={options || []}
      onTextChange={changeText}
      loading={loading}
      {...props}
    />
  );
};

export default ConnectedAutocompleteField;
