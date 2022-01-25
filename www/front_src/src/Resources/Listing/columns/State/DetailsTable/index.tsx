import React, { useState, useEffect } from 'react';

import { map, prop, sum, pipe } from 'ramda';

import {
  TableContainer,
  TableRow,
  Paper,
  Table,
  TableHead,
  TableCell,
  TableBody,
  Skeleton,
} from '@mui/material';

import {
  useRequest,
  getData,
  ListingModel,
  Column,
  ColumnType,
} from '@centreon/ui';

import {
  labelSomethingWentWrong,
  labelYes,
  labelNo,
} from '../../../../translatedLabels';

const getYesNoLabel = (value: boolean): string => (value ? labelYes : labelNo);

interface DetailsTableColumn extends Column {
  getContent: (details) => string | JSX.Element;
  id: string;
  label: string;
  type: ColumnType;
  width: number;
}

export interface DetailsTableProps {
  columns: Array<DetailsTableColumn>;
  endpoint: string;
}

const DetailsTable = <TDetails extends { id: number }>({
  endpoint,
  columns,
}: DetailsTableProps): JSX.Element => {
  const [details, setDetails] = useState<Array<TDetails> | null>();

  const { sendRequest } = useRequest<ListingModel<TDetails>>({
    request: getData,
  });

  useEffect(() => {
    sendRequest({
      endpoint,
    }).then((retrievedDetails) => {
      setDetails(retrievedDetails.result);
    });
  }, []);

  const loading = details === undefined;
  const error = details === null;
  const success = !loading && !error;

  const tableMaxWidth = pipe(map(prop('width')), sum)(columns);

  return (
    <TableContainer component={Paper}>
      <Table size="small" style={{ width: tableMaxWidth }}>
        <TableHead>
          <TableRow>
            {columns.map(({ label }) => (
              <TableCell key={label}>{label}</TableCell>
            ))}
          </TableRow>
        </TableHead>
        <TableBody>
          {loading && (
            <TableRow>
              <TableCell colSpan={columns.length}>
                <Skeleton animation="wave" height={20} />
              </TableCell>
            </TableRow>
          )}
          {success &&
            details?.map((detail) => (
              <TableRow key={detail.id}>
                {columns.map(({ label, getContent, width }) => (
                  <TableCell key={label} style={{ maxWidth: width }}>
                    <span>{getContent?.(detail)}</span>
                  </TableCell>
                ))}
              </TableRow>
            ))}
          {error && (
            <TableRow>
              <TableCell align="center" colSpan={columns.length}>
                <span>{labelSomethingWentWrong}</span>
              </TableCell>
            </TableRow>
          )}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export { getYesNoLabel };
export default DetailsTable;
