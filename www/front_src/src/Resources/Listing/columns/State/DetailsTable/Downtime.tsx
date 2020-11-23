import * as React from 'react';

import parse from 'html-react-parser';
import DOMPurify from 'dompurify';

import { makeStyles } from '@material-ui/core';

import { ColumnType, useLocaleDateTimeFormat } from '@centreon/ui';

import {
  labelAuthor,
  labelFixed,
  labelYes,
  labelNo,
  labelStartTime,
  labelEndTime,
  labelComment,
} from '../../../../translatedLabels';
import DetailsTable, { DetailsTableProps } from '.';

const useStyles = makeStyles({
  comment: {
    display: 'block',
    whiteSpace: 'nowrap',
    textOverflow: 'ellipsis',
    overflow: 'hidden',
  },
});

interface DowntimeDetails {
  comment: string;
  // eslint-disable-next-line react/no-unused-prop-types
  id: number;
}

type Props = Pick<DetailsTableProps, 'endpoint'>;

const DowntimeDetailsTable = ({ endpoint }: Props): JSX.Element => {
  const classes = useStyles();

  const { toDateTime } = useLocaleDateTimeFormat();

  const columns = [
    {
      id: 'author',
      label: labelAuthor,
      type: ColumnType.string,
      getContent: ({ author_name }): string => author_name,
      width: 100,
    },
    {
      id: 'is_fixed',
      label: labelFixed,
      type: ColumnType.string,
      getContent: ({ is_fixed }): string => (is_fixed ? labelYes : labelNo),
      width: 100,
    },
    {
      id: 'start_time',
      label: labelStartTime,
      type: ColumnType.string,
      getContent: ({ start_time }): string => toDateTime(start_time),
      width: 150,
    },
    {
      id: 'end_time',
      label: labelEndTime,
      type: ColumnType.string,
      getContent: ({ end_time }): string => toDateTime(end_time),
      width: 150,
    },

    {
      id: 'comment',
      label: labelComment,
      type: ColumnType.string,
      width: 250,
      getContent: ({ comment }: DowntimeDetails): JSX.Element => {
        return (
          <span className={classes.comment}>
            {parse(DOMPurify.sanitize(comment))}
          </span>
        );
      },
    },
  ];

  return (
    <DetailsTable<DowntimeDetails> columns={columns} endpoint={endpoint} />
  );
};

export default DowntimeDetailsTable;
